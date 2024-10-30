<?php
/**
 * @author chatwing
 * @package Chatwing\SDK
 */

namespace Chatwing;

use \Chatwing\Exception\ChatwingException;

class Object
{
    protected $data = array();

    protected $isDataChanged = false;

    public function __construct($data = array())
    {
        if (!empty($data)) {
            $this->setData($data);
        }
    }

    /**
     * @param $key
     * @param null $value
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
            $this->dataChanged();
        } else {
            $this->data[$key] = $value;
        }
    }

    /**
     * @param $key
     * @param null $default
     * @return null
     */
    public function getData($key = '', $default = null)
    {
        if (empty($key)) {
            return $this->data;
        } else {
            return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
        }
    }

    /**
     * @param string $key
     * @return $this|\Chatwing\Object [type]      [description]
     */
    public function unsetData($key = '')
    {
        if ($key) {
            if ($this->hasData($key)) {
                unset($this->data[$key]);
                $this->dataChanged();
            }

            return $this;
        } else {
            return $this->resetData();
        }
    }

    /**
     * Clear internal data and reset flag
     * @return $this
     */
    public function resetData()
    {
        $this->data = array();
        $this->dataChanged(false);
        return $this;
    }

    /**
     * @param $key
     * @param bool $forceData
     * @return bool
     */
    public function hasData($key = '', $forceData = true)
    {
        if ($key) {
            return $forceData ? isset($this->data[$key]) : array_key_exists($key, $this->data);
        } else {
            return !empty($this->data);
        }
    }

    /**
     * @param  bool $flag
     * @return Object
     */
    public function dataChanged($flag = true)
    {
        $this->isDataChanged = $flag;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDirty()
    {
        return $this->isDataChanged;
    }

    public function __call($name, $argument)
    {
        switch (substr($name, 0, 3)) {
            case 'set':
                $key = $this->underscoreize(substr($name, 3));
                $this->setData($key, isset($argument[0]) ? $argument[0] : null);
                return $this;
                break;

            case 'get':
                $key = $this->underscoreize(substr($name, 3));
                return $this->getData($key, isset($argument[0]) ? $argument[0] : null);
                break;

            default:
                throw new ChatwingException(array('message' => __('Method not found', CHATWING_TEXTDOMAIN)));
        }
    }

    /**
     * Convert from camel-case string to underscore lowercase string
     * Eg: CamelCase => camel_case
     *
     * @param $str string
     *
     * @return mixed
     */
    protected function underscoreize($str)
    {
        return strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $str));
    }
} 