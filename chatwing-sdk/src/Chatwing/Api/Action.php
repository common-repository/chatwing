<?php
/**
 * @author chatwing <dev@chatwing.com>
 * @package Chatwing\SDK\Api
 */

namespace Chatwing\Api;

use \Chatwing\Object;
use \Chatwing\Exception\ChatwingException;

/**
 * Class Action
 *
 * @package Chatwing\Api
 * @method getType() string
 */
class Action extends Object
{
    private static $actionList = array();

    /**
     * Constructor of Action object. Throw exception if action is not found
     *
     * @param       $name
     * @param array $params
     *
     * @throws \Chatwing\Exception\ChatwingException
     */
    public function __construct($name, $params = array())
    {
        if (empty(self::$actionList)) {
            self::loadActionList();
        }

        $this->setCurrentAction($name);
        $this->setData('params', $params);
    }

    public function getParams()
    {
        return $this->getData('params');
    }

    /**
     * @return null
     */
    public function getActionUri()
    {
        return $this->getData('name');
    }

    /**
     * @param $actionName
     * @return bool
     */
    public function isActionValid($actionName)
    {
        return isset(self::$actionList[$actionName]) && !empty(self::$actionList[$actionName]);
    }

    public function isAuthenticationRequired()
    {
        return $this->hasData('auth') && $this->getData('auth');
    }

    /**
     * @param null $path
     * @throws ChatwingException
     */
    protected static function loadActionList($path = null)
    {
        if (is_null($path)) {
            if (!defined('CHATWING_BASE_DIR')) {
                define('CHATWING_BASE_DIR', dirname(dirname(__FILE__)));
            }
            $path = dirname(CHATWING_BASE_DIR) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'actions.php';
        }

        if (file_exists($path)) {
            self::$actionList = include $path;
        } else {
            throw new ChatwingException(array('message' => __("Action list not found", CHATWING_TEXTDOMAIN), 'code' => 0));
        }
    }

    /**
     * @param $actionName
     * @throws ChatwingException
     */
    private function setCurrentAction($actionName)
    {
        if (!$this->isActionValid($actionName)) {
            throw new \InvalidArgumentException('Invalid action');
        }
        $this->setData('name', $actionName);
        foreach (self::$actionList[$actionName] as $key => $value) {
            $this->setData($key, $value);
        }
    }
} 