<?php
/**
 * @author chatwing <dev@chatwing.com>
 * @package Chatwing\SDK
 */

namespace Chatwing;

use ArrayAccess;

/**
 * Class Container
 * @package Chatwing
 */
class Container implements ArrayAccess
{
    protected $storage = array();

    /**
     * Create new binding
     * @param  string $key 
     * @param  mixed $value     
     * @throws \Exception If attempt to bind an existing frozen key
     * @return $this
     */
    public function bind($key, $value)
    {
        if ($this->isFrozen($key)) {
            throw new \Exception( __("Can not overwrite the element with key '{$key}' !! ", CHATWING_TEXTDOMAIN));
        }

        $this->storage[$key] = array(
            'value' => $value,
            'factory' => false,
            'invoked' => is_callable($value) ? false : true
        );

        return $this;
    }

    /**
     * Alias to bind() method, but does n't set the namespace
     * @param string $key
     * @param mixed $value
     * @return \Chatwing\Container
     * @deprecated Will be removed later
     */
    protected function set($key, $value)
    {
        return $this->bind($key, $value);
    }

    /**
     * Register an callable as factory
     * @param  string $key       
     * @param  mixed $callable  
     * @throws \Exception
     * @return $this            
     */
    public function factory($key, $callable)
    {
        if ($this->isFrozen($key)) {
            throw new \Exception("Can not overwrite the element with key '{$key}' !! ");
        }

        if (!(is_object($callable) && method_exists($callable, '__invoke'))) {
            throw new \Exception(__("Value should be a Closure or object/class with __invoke method!!", CHATWING_TEXTDOMAIN));
        }

        $this->storage[$key] = array(
            'value' => $callable,
            'factory' => true
        );

        return $this;
    }

    /**
     *                 
     * @param  string $key      
     * @throws \Exception If key doesn't exist
     * @return mixed|null           
     */
    public function get($key)
    {
        if (!$this->has($key)) {
            throw new \Exception("Key {$key} does not exist!");
        }

        if ($this->storage[$key]['factory']) {
            return $this->storage[$key]['value']($this);
        }

        if (!$this->storage[$key]['invoked']) {
            $this->storage[$key]['value'] = $this->storage[$key]['value']($this);
            $this->storage[$key]['invoked'] = true;
        }

        return $this->storage[$key]['value'];
    }

    /**
     *
     * @param  string $key
     * @return boolean
     */
    public function has($key)
    {
        return isset($this->storage[$key]);
    }

    /**
     * 
     * @param  string $key
     * @throws \Exception If key doesn't exist
     * @return $this     
     */
    public function remove($key)
    {
        if (!$this->has($key)) {
            throw new \Exception("Key {$key} does not exist !!");
        }
        unset($this->storage[$key]);
        return $this;
    }

    /**
     * Check if key is frozen
     * @param  [type]  $key [description]
     * @return boolean      [description]
     */
    public function isFrozen($key)
    {
        if (!$this->has($key)) {
            return false;
        }

        return isset($this->storage[$key]['frozen']) ? $this->storage[$key]['frozen'] : false;
    }

    /**
     * Freeze an element
     * @param $key
     * @throws \Exception
     * @internal param $ [type] $key [description]
     * @return $this [type]      [description]
     */
    public function freeze($key)
    {
        if (!$this->has($key)) {
            throw new \Exception("Key {$key} does not exist!");
        }

        $this->storage[$key]['frozen'] = true;
        return $this;
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }


    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->bind($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->remove($offset);
    }
}
