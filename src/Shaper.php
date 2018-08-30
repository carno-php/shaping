<?php
/**
 * Shaper
 * User: moyo
 * Date: 19/08/2017
 * Time: 11:02 AM
 */

namespace Carno\Shaping;

use function Carno\Coroutine\race;
use function Carno\Coroutine\timeout;
use Carno\Promise\Promise;
use Carno\Promise\Promised;
use Carno\Shaping\Exception\AcquirePermitsDeniedException;
use Carno\Shaping\Exception\AcquireWaitTimeoutException;
use SplStack;

class Shaper
{
    /**
     * @var Options
     */
    private $options = null;

    /**
     * @var Bucket
     */
    private $bucket = null;

    /**
     * @var SplStack
     */
    private $waits = null;

    /**
     * Shaper constructor.
     * @param Options $options
     */
    public function __construct(Options $options)
    {
        $this->options = $options;

        $this->waits = new SplStack;

        $this->bucket = new Bucket($options, function (Bucket $bucket) {
            $this->continues($bucket);
        });

        Control::register($this);
    }

    /**
     */
    public function shutdown() : void
    {
        $this->bucket->stop();

        Control::deregister($this);
    }

    /**
     * @return int
     */
    public function tokens() : int
    {
        return $this->bucket->tokens();
    }

    /**
     * @return int
     */
    public function waits() : int
    {
        return $this->waits->count();
    }

    /**
     * @param Bucket $bucket
     */
    private function continues(Bucket $bucket) : void
    {
        if ($this->waits->isEmpty()) {
            return;
        }

        while (!$this->waits->isEmpty()) {
            /**
             * @var Promised $wait
             */
            list($permits, $wait) = $this->waits->shift();
            if ($wait->pended()) {
                if ($bucket->out($permits)) {
                    $wait->resolve();
                } else {
                    $this->waits->unshift([$permits, $wait]);
                    break;
                }
            }
        }
    }

    /**
     * @param int $permits
     * @return bool
     */
    public function acquired(int $permits = 1) : bool
    {
        return $this->bucket->out($permits) ? true : false;
    }

    /**
     * @param int $permits
     * @return Promised
     */
    public function queued(int $permits = 1) : Promised
    {
        if ($this->options->waitQMax && $this->waits->count() < $this->options->waitQMax) {
            $queued = Promise::deferred();
            $this->waits->push([$permits, $queued]);
            return race($queued, timeout($this->options->waitTimeout, AcquireWaitTimeoutException::class));
        } else {
            return Promise::rejected(new AcquirePermitsDeniedException);
        }
    }
}
