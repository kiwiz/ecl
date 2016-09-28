<?php

namespace ECL\Command;

/**
 * Load command
 * Loads results from the given symbols.
 */
class Load extends \ECL\Command {
    /** @var string[] The symbols to load. */
    private $sources = [];

    /**
     * @param string[] $sources Symbol names.
     */
    public function __construct($sources) {
        $this->sources = $sources;
    }

    public function process(\ECL\SymbolTable $table) {
        $ret = [];
        $keys = [];
        foreach($this->sources as $source) {
            $res = $table->resolve(new \ECL\Symbol($source), \ECL\Symbol::T_RES);
            $ret = array_merge($ret, $res->getAll());
            $keys = array_merge($keys, $res->getKeys());
        }

        return new \ECL\ResultSet($ret, array_unique($keys));
    }
}
