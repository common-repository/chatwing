<?php
/**
 * @author chatwing <dev@chatwing.com>
 * @package Chatwing\SDK
 */
namespace Chatwing;

class Application extends Container
{
    /**
     * @var Container
     */
    protected static $container = null;

    public static function getInstance()
    {
        if (is_null(static::$container)) {
            static::$container = new static();
        }

        return static::$container;
    }
}
