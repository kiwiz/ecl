<?php

namespace ECL;

/**
 * Class ArrayUnion
 * Stores a collection of arrays and allows read access to them.
 * @package ECL
 */
class ArrayUnion implements \ArrayAccess {
    /** @var array[] Arrays to expose. */
    private $arrays = [];

    /**
     * @param array[] Arrays.
     */
    public function __construct($arrays) {
        $this->arrays = $arrays;
    }

    /**
     * Get a list of all defined keys.
     * @return string[] Keys.
     */
    public function getKeys() {
        $keys = [];
        foreach($this->arrays as $array) {
            $keys = array_merge($keys, $array instanceof \ArrayAccess ? $array->getKeys():array_keys($array));
        }
        return array_unique($keys);
    }

    /**
     * Get the value for a symbol.
     * @param string $key Symbol name.
     * @return Value Symbol value.
     */
    public function &offsetGet($key) {
        for($i = 0; $i < count($this->arrays); ++$i) {
            if(array_key_exists($key, $this->arrays[$i])) {
                return $this->arrays[$i][$key];
            }
        }
        throw new KeyNotFoundException($key);
    }
    /**
     * Set the value for a key.
     * @param string $key Symbol name.
     * @param Value $value Symbol value.
     */
    public function offsetSet($key, $val) {
        throw new NotImplementedException;
    }
    /**
     * Check if a key exists.
     * @param string $key Key.
     * @return bool Whether the symbol exists.
     */
    public function offsetExists($key) {
        foreach($arrays as $array) {
            if(array_key_exists($key, $array)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Delete a key.
     * @param string $key Key.
     */
    public function offsetUnset($key) {
        throw new NotImplementedException;
    }
}

class NotImplementedException extends Exception {}
