<?php
/**
 * Shaper's control
 * User: moyo
 * Date: 22/02/2018
 * Time: 3:24 PM
 */

namespace Carno\Shaping;

use Closure;

class Control
{
    /**
     * @var Shaper[]
     */
    private static $instances = [];

    /**
     * @param Shaper $shaper
     */
    public static function register(Shaper $shaper) : void
    {
        self::$instances[spl_object_id($shaper)] = $shaper;
    }

    /**
     * @param Shaper $shaper
     */
    public static function deregister(Shaper $shaper) : void
    {
        unset(self::$instances[spl_object_id($shaper)]);
    }

    /**
     * @param Closure $receiver
     */
    public static function retrieving(Closure $receiver) : void
    {
        array_walk(self::$instances, function (Shaper $shaper) use ($receiver) {
            $receiver($shaper);
        });
    }
}
