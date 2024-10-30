<?php

/**
 * @author chatwing
 * @package Chatwing_SDK
 */

if (!defined('CW_DEBUG')) {
    define('CW_DEBUG', false);
}

define('CW_SDK_VESION', '1.0');
define('CW_ENV_DEVELOPMENT', 'development');
define('CW_ENV_PRODUCTION', 'production');

use Chatwing\Application as App;

$app = App::getInstance();
$app->bind(
    'api',
    function (\Chatwing\Container $container) {
        $app = new Chatwing\Api($container->get('client_id'));
        
        $app->setEnv(
            defined('CW_USE_STAGING') && CW_USE_STAGING ? CW_ENV_DEVELOPMENT : CW_ENV_PRODUCTION
        );

        if ($container->has('access_token')) {
            $app->setAccessToken($container->get('access_token'));
        }

        return $app;
    }
);

$app->factory(
    'chatbox',
    function (\Chatwing\Container $container) {
        return new \Chatwing\Chatbox($container->get('api'));
    }
);