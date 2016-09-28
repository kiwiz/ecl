<?php

namespace ECL\Command\Sort;

/**
 * Sort command
 * Sort entries in the result by one or more keys.
 */
class Fields extends \ECL\Command\Sort {
    /** @var array A mapping of keys to sort order. */
    private $clauses = [];

    /** Sort in ascending order. */
    const T_ASC = 0;
    /** Sort in descending order. */
    const T_DESC = 1;

    /**
     * @param array $clauses Sort clauses.
     */
    public function __construct(array $clauses) {
        $this->clauses = $clauses;
    }

    public function process(\ECL\SymbolTable $table) {
        $result = $table[\ECL\SymbolTable::DEFAULT_SYMBOL];

        // Generate the table of mappings for array_multisort.
        $sort = [];
        $entries = $result->getAll();
        foreach($this->clauses as $src_clause) {
            list($key, $order) = $src_clause;
            $key = $table->resolve($key, \ECL\Symbol::T_STR);
            $sort_clause = [];
            foreach($entries as $entry) {
                $sort_clause[] = \ECL\Util::get($entry, $key);
            }

            $sort[] = $sort_clause;
            $sort[] = $order == self::T_ASC ? SORT_ASC:SORT_DESC;
        }

        if(count($sort) > 0) {
            $sort[] = &$entries;
            call_user_func_array('array_multisort', $sort);
        }

        return new \ECL\ResultSet($entries);
    }
}
