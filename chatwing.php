<?php
/**
 * @package Chatwing\IntegrationPlugins\Wordpress
 */

/*
Plugin Name: Chatwing Live Group Chat - HTML5 + Chat Apps
Plugin URI: http://chatwing.com/
Description: Chatwing offers an unlimited live website or blog chat experience. This chat widget specializes in delivering real-time communication at any given time. Engage in a free chat with visitors and friends!
Version: 2.4.5
Author: chatwing
Author URI: http://chatwing.com/
License: GPLv2 or later
Text Domain: chatwing
*/

define('CHATWING_VERSION', '2.4.5');
define('CHATWING_TEXTDOMAIN', 'chatwing');
define('CHATWING_PATH', dirname(__FILE__));
define('CHATWING_CLASS_PATH', CHATWING_PATH . '/classes');
define('CHATWING_TPL_PATH', CHATWING_PATH . '/templates');
define('CHATWING_PLG_MAIN_FILE', __FILE__);
define('CHATWING_PLG_URL', plugin_dir_url(__FILE__));

define('CHATWING_DEBUG', false);
define('CW_USE_STAGING', false);

define('CHATWING_CLIENT_ID', 'wordpress');

require_once CHATWING_PATH . '/chatwing-sdk/src/Chatwing/autoloader.php';
require_once CHATWING_PATH . '/chatwing-sdk/src/Chatwing/start.php';
require_once CHATWING_PATH . '/oauth.php';
require_once CHATWING_PATH . '/registration.php';
$keyPath = CHATWING_PATH . '/key.php';
if (file_exists($keyPath)) {
    require $keyPath;
}

/**
 * Plugin class autoloader
 * @param  $className
 * @return bool
 * @throws Exception
 */
function chatwingAutoloader($className)
{
    $prefix = 'Chatwing\\IntegrationPlugins\\WordPress\\';

    if ($pos = strpos($className, $prefix) !== 0) {
        return false;
    }

    $filePath = CHATWING_CLASS_PATH . '/' . str_replace('\\', '/', substr($className, strlen($prefix))) . '.php';
    if (file_exists($filePath)) {
        require_once($filePath);

        if (!class_exists($className)) {
            throw new Exception(__("Class {$className} doesn't exist ", CHATWING_TEXTDOMAIN));
        }

        return true;
    } else {
        throw new Exception(__("Cannot find file at {$filePath} ", CHATWING_TEXTDOMAIN));
    }
}

function chatwing_text_domain() {
    load_plugin_textdomain( 'chatwing', WP_PLUGIN_DIR . '/chatwing/'. 'languages', basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'chatwing_text_domain' );

spl_autoload_register('chatwingAutoloader');

use Chatwing\Application as Chatwing;
use Chatwing\IntegrationPlugins\WordPress\Application;
use Chatwing\IntegrationPlugins\WordPress\DataModel;

Chatwing::getInstance()->bind('client_id', CHATWING_CLIENT_ID);
$app = new Application(DataModel::getInstance());
$app->run();