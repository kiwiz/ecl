<?php

namespace ECL\Command;

/**
 * Join command
 * Join entries in the result by a target key.
 */
class Join extends \ECL\Command {
    /** @var string The ResultSet to join with. */
    private $source = null;
    /** @var string[] The keys to group on. */
    private $keys = [];

    /**
     * @param string $type Joining ResultSet.
     * @param string[] $keys Joining keys.
     */
    public function __construct($source, $keys) {
        $this->source = $source;
        $this->keys = $keys;
    }

    public function process(\ECL\SymbolTable $table) {
        $result = $table[\ECL\SymbolTable::DEFAULT_SYMBOL];
        $source = $table->resolve(new \ECL\Symbol($this->source), \ECL\Symbol::T_RES);
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
            if($ok) {
                $ret[] = array_merge($node, $entry);
            }
        }

        return new \ECL\ResultSet($ret);
    }
}
