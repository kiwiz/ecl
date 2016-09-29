<?php

namespace ECL;

/**
 * Class SymbolTable
 * Stores a collection of symbols and their associated values.
 * @package ECL
 */
class SymbolTable implements \ArrayAccess {
    /** Default symbol. */
    const DEFAULT_SYMBOL = '_';

    /** @var Value[] Mapping of symbols to values. */
    private $table = [];

    /**
     * Check if the input is a Symbol and resolve it if so.
     * @param Symbol|mixed $sym A symbol object or builtin type.
     * @param int $type Data type.
     * @return mixed A resolved symbol or the passed in data.
     */
    public function resolve($sym, $type=Symbol::T_NULL) {
        $val = $sym;
        if($sym instanceof \ECL\Symbol) {
           $val = $this->offsetGet($sym->getName());
           if($type == Symbol::T_NULL) {
               $type = $sym->getType();
           }
        }

        switch($type) {
        case Symbol::T_INT:
            $val = (int) $val;
            break;
        case Symbol::T_FLOAT:
            $val = (double) $val;
            break;
        case Symbol::T_STR:
            $val = (string) $val;
            break;
        case Symbol::T_LIST:
            if(!($val instanceof \ECL\ResultSet)) {
                throw new WrongTypeException($sym->getName());
            }
            $val = array_unique(\ECL\Util::pluck($val->getAll(), $sym->getPath()));
            break;
        case Symbol::T_RES:
            if(!($val instanceof \ECL\ResultSet)) {
                throw new WrongTypeException($sym->getName());
            }
            break;
        }

        return $val;
    }

    /**
     * Get the value for a symbol.
     * @param string $key Symbol name.
     * @return Value Symbol value.
     */
    public function &offsetGet($key) {
        if(!$this->offsetExists($key)) {
            throw new KeyNotFoundException($key);
        }
        return $this->table[$key];
    }
    /**
     * Set the value for a symbol.
     * @param string $key Symbol name.
     * @param Value $value Symbol value.
     */
    public function offsetSet($key, $val) {
        $this->table[$key] = $val;
    }
    /**
     * Check if a symbol exists.
     * @param string $key Symbol name.
     * @return bool Whether the symbol exists.
     */
    public function offsetExists($key) {
        return array_key_exists($key, $this->table);
    }
    /**
     * Delete a symbol.
     * @param string $key Symbol name.
     */
    public function offsetUnset($key) {
        unset($this->table[$key]);
    }

    /**
     * Get a list of all defined keys.
     * @return string[] Keys.
     */
    public function getKeys() {
        return array_keys($this->table);
    }
}

class KeyNotFoundException extends Exception {}
class WrongTypeException extends Exception {}
