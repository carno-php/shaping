<?php
/**
 * Bucket with tokens
 * User: moyo
 * Date: 19/08/2017
 * Time: 11:42 AM
 */

namespace Carno\Shaping;

use Carno\Timer\Timer;

class Bucket
{
    /**
     * Overall generating interval in ms
     */
    private const GMS = 1000;

    /**
     * How many TICK in once generating loop
     */
    private const PST = 10;

    /**
     * @var Options
     */
    private $options = null;

    /**
     * @var int
     */
    private $tokens = 0;

    /**
     * @var string
     */
    private $generator = null;

    /**
     * @var callable
     */
    private $watcher = null;

    /**
     * Bucket constructor.
     * @param Options $options
     * @param callable $watcher
     */
    public function __construct(Options $options, callable $watcher = null)
    {
        $this->options = $options;

        $this->tokens = $options->capacity;

        $this->watcher = $watcher;
        $this->generator = Timer::loop(self::GMS / self::PST, [$this, 'in']);
    }

    /**
     */
    public function stop() : void
    {
        Timer::clear($this->generator);
    }

    /**
     * @return int
     */
    public function tokens() : int
    {
        return $this->tokens;
    }

    /**
     * tokens in
     */
    public function in() : void
    {
        $this->tokens += intval($this->options->bucketTGS / self::PST);

        $this->options->capacity > 0
            ? $this->tokens > $this->options->capacity && $this->tokens = $this->options->capacity
            : $this->tokens > $this->options->bucketTGS && $this->tokens = $this->options->bucketTGS
        ;

        ($c = $this->watcher) && $c($this);
    }

    /**
     * tokens out
     * @param int $n
     * @return bool
     */
    public function out(int $n) : bool
    {
        if ($n <= $this->tokens) {
            $this->tokens -= $n;
            return true;
        } else {
            return false;
        }
    }
}
