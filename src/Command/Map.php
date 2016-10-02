<?php

namespace ECL\Command;

/**
 * Map command
 * Transform the result from the previous command.
 */
class Map extends \ECL\Command {
    /** @var array An array of map operations to execute. */
    private $clauses = [];

    /** Combine multiple keys into a new key. */
    const T_COMBINE = 0;
    /** Delete a key. */
    const T_DELETE = 1;
    /** Map a key via an expression. */
    const T_EXPR = 2;

    /**
     * @param array $clauses Map operations.
     */
    public function __construct(array $clauses) {
        $this->clauses = $clauses;
    }

    public function process(\ECL\SymbolTable $table) {
        $result = $table[\ECL\SymbolTable::DEFAULT_SYMBOL];
        $ret = [];

        $key_cache = [];
        foreach($result->getAll() as $entry) {
            foreach($this->clauses as $clause) {
                switch($clause[0]) {
                case self::T_COMBINE:
                    // Combine contents of these keys.
                    $val_arr = [];
                    foreach($clause[1] as $key) {
                        $key = $table->resolve($key, \ECL\Symbol::T_STR);
                        $val = \ECL\Util::get($entry, $key);
                        if(!is_null($val)) {
                            $val_arr[] = $val;
                        }
                    }
                    $dest = $table->resolve($clause[2], \ECL\Symbol::T_STR);
                    $entry[$dest] = implode('', $val_arr);
                    break;
                case self::T_DELETE:
                    // Delete this key.
                    $key = $table->resolve($clause[1], \ECL\Symbol::T_STR);
                    unset($entry[$key]);
                    break;
                case self::T_EXPR:
                    // Map this key via an SEL expression.
                    $key = $table->resolve($clause[1], \ECL\Symbol::T_STR);
                    $expr = $table->resolve($clause[2], \ECL\Symbol::T_STR);
                    if(array_key_exists($key, $entry)) {
                        $el = new \ECL\ExpressionLanguage;
                        $entry[$key] = $el->evaluate($expr, new \ECL\ArrayUnion([['_' => $entry[$key]], $table]));
                    }
                    break;
                }
            }
            $ret[] = $entry;
        }

        return new \ECL\ResultSet($ret);
    }
}
