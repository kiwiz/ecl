<?php

namespace ECL;

/**
 * Class Util
 * Useful methods.
 * @package ECL
 */
class Util {
    /**
     * Return the value of a key or a default value.
     * @param mixed $arr The array.
     * @param string $key The key.
     * @param mixed $default The default value to return.
     * @return mixed|null The value of that key.
     */
    public static function get($arr, $key, $default=null) {
        return self::exists($arr, $key) ? $arr[$key]:$default;
    }

    /**
     * Determines whether an array contains a certain key.
     * @param mixed $arr The array.
     * @param string $key The key.
     * @return bool true if the key exists and false otherwise.
     */
    public static function exists($arr, $key) {
        // If it's an object, index directly because array_key_exists doesn't
        // work with the ArrayAccess interface. Otherwise, check if it implements
        // ArrayAccess and fall back to array_key_exists.
        if(is_object($arr)) {
            return isset($arr[$key]);
        }
        if(is_array($arr)) {
            return array_key_exists($key, $arr);
        }

        return false;
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
