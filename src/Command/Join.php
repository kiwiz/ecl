<?php

namespace ECL\Command;

/**
 * Join command
 * Join entries in the result by a target key.
 */
class Join extends \ECL\Command {
    /** @var int The type of join. */
    private $type = 0;
    /** @var string The ResultSet to join with. */
    private $source = null;
    /** @var string[] The keys to group on. */
    private $keys = [];

    /** Inner join. */
    const T_INNER = 0;
    /** Left join. */
    const T_LEFT = 1;
    /** Right join. */
    const T_RIGHT = 2;

    /**
     * @param string $type Joining ResultSet.
     * @param string[] $keys Joining keys.
     */
    public function __construct($source, $keys, $type) {
        $this->source = $source;
        $this->keys = $keys;
        $this->type = $type;
    }

    public function process(\ECL\SymbolTable $table) {
        $result = $table[\ECL\SymbolTable::DEFAULT_SYMBOL];
        $source = $table->resolve(new \ECL\Symbol($this->source), \ECL\Symbol::T_RES);
        // Convert a left join into a right join.
        if($this->type == self::T_LEFT) {
            $temp = $result;
            $result = $source;
            $source = $temp;
            $this->type = self::T_RIGHT;
        }
        $ret = [];

        // Generate a mapping of keys.
        $mapping = [];
        foreach($result->getAll() as $entry) {
            $node = &$mapping;

            foreach($this->keys as $key) {
                $val = sha1(json_encode(\ECL\Util::get($entry, $key, null)));

                if(!array_key_exists($val, $node)) {
                    $node[$val] = [];
                }
                $node = &$node[$val];
            }
            if(count($node) == 0) {
                $node = $entry;
            }
        }

        // Check the mapping for a match. If found, merge the two entries and add to the
        // return buffer.
        foreach($source->getAll() as $entry) {
            $node = &$mapping;
            $ok = true;

            foreach($this->keys as $key) {
                $val = sha1(json_encode(\ECL\Util::get($entry, $key, null)));

                if(!array_key_exists($val, $node)) {
                    $ok = false;
                    break;
                }
                $node = &$node[$val];
            }
            // If there is a match, merge them and output.
            if($ok) {
                $ret[] = array_merge($node, $entry);
            // If it's a left join, add the row anyway.
            } else if($this->type == self::T_RIGHT) {
                $ret[] = $entry;
            }
        }

        return new \ECL\ResultSet($ret);
    }
}
