<?php

namespace ECL;

/**
 * Class Result
 * Represents an ECL result.
 * @package ECL
 */
class ResultSet implements \ArrayAccess, \Countable, \IteratorAggregate {
    /** @var string[] An array of keys. */
    private $keys = [];
    /** @var mixed[] Result data. */
    private $data = [];

    public function __construct(array $data, array $keys=null) {
        $this->data = $data;
        if(is_null($keys)) {
            $keys = [];
            foreach($data as $row) {
                foreach(array_keys($row) as $key) {
                    $keys[$key] = null;
                }
            }
            $keys = array_keys($keys);
        }
        $this->keys = $keys;
    }

    /**
     * Get all result data.
     * @return array[] Result data.
     */
    public function getAll() {
        return $this->data;
    }

    /**
     * Get the list of keys for the result data.
     * @return string[] A list of keys.
     */
    public function getKeys() {
        return $this->keys;
    }

    /**
     * Get the nth entry.
     * @param int $n Index.
     * @return mixed[] Value.
     */
    public function &offsetGet($n) {
        if(!$this->offsetExists($n)) {
            throw new IndexNotFoundException((string) $n);
        }
        return $this->data[$n];
    }
    /**
     * Set the nth entry.
     * @param int $n Index.
     * @param mixed[] $val Value.
     */
    public function offsetSet($n, $val) {
        throw new \RuntimeException('Unsupported');
    }
    /**
     * Check if the index exists.
     * @param int $n Index.
     * @return bool Whether the index exists.
     */
    public function offsetExists($n) {
        return $n >= 0 && $n < count($this->data);
    }
    /**
     * Delete an entry.
     * @param int $key Index.
     */
    public function offsetUnset($key) {
        throw new \RuntimeException('Unsupported');
    }

    /**
     * Get a count of rows.
     * @return int Row count.
     */
    public function count() {
        return count($this->data);
    }

    /**
     * Get an iterator over the contents
     * @return ResultSetIterator Iterator.
     */
    public function getIterator() {
        return new ResultSetIterator($this);
    }
}

class ResultSetIterator implements \Iterator {
    private $i = 0;
    /** @var ResultSet */
    private $result;

    /**
     * @param ResultSet $result
     */
    public function __construct($result) {
        $this->result = $result;
    }
    public function current() { return $this->result->offsetGet($this->i); }
    public function key() { return $this->i; }
    public function next() { ++$this->i; }
    public function rewind() { $this->i = 0; }
    public function valid() { return $this->i < count($this->result); }
}

class IndexNotFoundException extends Exception {}
