<?php

namespace ECL;

/**
 * Class Util
 * Useful methods.
 * @package ECL
 */
class Util {
    public static function get($arr, $key, $default=null) {
        return array_key_exists($key, $arr) ? $arr[$key]:$default;
    }

    public static function exists($arr, $key) {
        return array_key_exists($key, $arr);
    }

    /**
     * Extract values from an array with the given path.
     * @param array $arr
     * @param string|string[] $path
     * @return array
     */
    public static function pluck($arr, $path) {
        $ret = [];
        $path = (array) $path;
        foreach($arr as $v) {
            $node = &$v;
            $ok = true;

            foreach($path as $part) {
                if(!is_array($node) || !array_key_exists($part, $node)) {
                    $ok = false;
                    break;
                }
                $node = &$node[$part];
            }

            if($ok) {
                $ret[] = $node;
            }
        }
        return $ret;
    }

    // Given two timestamps, return the inclusive list of dates between them.
    public static function getIndices($index, $from_ts, $to_ts) {
        $dates = [];
        $current = new \DateTime("@$from_ts");
        $to = new \DateTime("@$to_ts");
        // Zero out the time component.
        $current->setTime(0, 0);
        $to->setTime(0, 0);
        while ($current <= $to) {
            $dates[] = sprintf('%s-%s', $index, $current->format('Y.m.d'));
            $current = $current->modify('+1day');
        }
        return $dates;
    }

    public static function combine($first, $rest, $idx) {
        $ret = [];
        $ret[] = $first;

        foreach($rest as $val) {
            $ret[] = $val[$idx];
        }
        return $ret;
    }

    public static function merge($first, $rest, $idx) {
        $ret = [];
        $ret = $first;

        foreach($rest as $val) {
            $ret = array_merge($ret, $val[$idx]);
        }
        return $ret;
    }

    public static function assoc($first, $rest, $idx) {
        $ret = [];
        $ret[$first[0]] = $first[1];

        foreach($rest as $val) {
            $ret[$val[$idx][0]] = $val[$idx][1];
        }
        return $ret;
    }
}
