<?php
/**
 * Shaper options
 * User: moyo
 * Date: 21/08/2017
 * Time: 6:32 PM
 */

namespace Carno\Shaping;

class Options
{
    /**
     * Capacity size. "0" = Leaky bucket, ">0" = Token bucket
     * @var int
     */
    public $capacity = 0;

    /**
     * Token's generating rate for bucket
     * @var int
     */
    public $bucketTGS = 0;

    /**
     * Max buffer size. more will be ignored
     * @var int
     */
    public $waitQMax = 0;

    /**
     * Wait timeout[ms] in buffer queue
     * @var int
     */
    public $waitTimeout = 0;

    /**
     * Options constructor.
     * @param int $capacity
     * @param int $bucketTGS
     * @param int $waitQMax
     * @param int $waitTimeout
     */
    public function __construct(
        int $capacity = 0,
        int $bucketTGS = 20000,
        int $waitQMax = 100000,
        int $waitTimeout = 2000
    ) {
        $this->capacity = $capacity;
        $this->bucketTGS = $bucketTGS;
        $this->waitQMax = $waitQMax;
        $this->waitTimeout = $waitTimeout;
    }
}
