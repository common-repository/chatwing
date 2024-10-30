<?php
/**
 * @author chatwing
 * @package Chatwing\SDK\Api
 */

namespace Chatwing\Api;

use ArrayAccess;

class Response implements ArrayAccess
{
    protected $data = array();

    public function __construct($data = array())
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * @return boolean [description]
     */
    public function isSuccess()
    {
        return $this->offsetExists('success') && $this->offsetGet('success');
    }

    /**
     * @return boolean [description]
     */
    public function isError()
    {
        return !$this->isSuccess();
    }

    /**
     * return HTTP code from response
     * @return string|null
     */
    public function getHttpCode()
    {
        return $this->offsetExists('http_code') ? $this->offsetGet('http_code') : null;
    }

    /**
     * Get the API error code
     * @return string|null 
     */
    public function getErrorCode()
    {
        return $this->offsetExists('error.code') ? $this->offsetGet('error.code') : null;
    }

    /**
     * get error message
     * @return string|null
     */
    public function getMessage()
    {
        return $this->offsetExists('error.message') ? $this->offsetGet('error.message') : null;
    }

    /**
     * get error description
     * @return string|null 
     */
    public function getErrorDescription()
    {
        return $this->offsetExists('error.params.description') ? $this->offsetGet('error.params.description') : null;
    }

    public function get($key, $default = null)
    {
        try {
            return $this->offsetGet($key);
        } catch (\Exception $e) {
            return $default;
        }
    }

    public function __toString()
    {
        return json_encode($this);
    }

    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        $offset = explode('.', $offset);
        $existed = true;
        $tmp = $this->data;

        foreach ($offset as $o) {
            if (isset($tmp[$o])) {
                $tmp = $tmp[$o];
            } else {
                $existed = false;
                break;
            }
        }

        return $existed;
    }

    /**
     * @param mixed $offset
     * @return mixed|void
     * @throws \Exception
     */
    public function offsetGet($offset)
    {
        $offset = explode('.', $offset);
        $existed = true;
        $tmp = $this->data;

        foreach ($offset as $o) {
            if (isset($tmp[$o])) {
                $tmp = $tmp[$o];
            } else {
                $existed = false;
                break;
            }
        }

        if ($existed) {
            return $tmp;
        }

        throw new \InvalidArgumentException("Index {$offset} does not exist");
    }

    /**
     * Internal setter
     * @param $key
     * @param null $value
     */
    protected function set($key, $value = null)
    {
        $this->data[$key] = $value;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
    }
}
