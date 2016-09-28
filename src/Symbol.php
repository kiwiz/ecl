<?php

namespace ECL;

/**
 * Class Symbol
 * Represents a reference.
 * @package ECL
 */
class Symbol {
    /** Symbol types. */
    const T_NULL = 0;
    const T_INT = 1;
    const T_FLOAT = 2;
    const T_STR = 3;
    const T_LIST = 4;
    const T_RES = 5;

    /** @var string Symbol name. */
    private $name = null;

    /** @var int Symbol type. */
    private $type = null;

    /** @var string[] Symbol path. */
    private $path = [];

    /**
     * @param string $name
     * @param int $type
     * @param string[] $path
     */
    public function __construct($name, $type=self::T_NULL, $path=[]) {
        $this->name = $name;
        $this->type = $type;
        $this->path = $path;
    }

    public function getName() {
        return $this->name;
    }

    public function getType() {
        return $this->type;
    }

    public function getPath() {
        return $this->path;
    }
}
