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
     * @param array|\ArrayAccess $arr The array.
     * @param string|int $key The key.
     * @param mixed $default The default value to return.
     * @return mixed|null The value of that key.
     */
    public static function get($arr, $key, $default=null) {
        return self::exists($arr, $key) ? $arr[$key]:$default;
    }

    /**
     * Determines whether an array contains a certain key.
     * @param array|\ArrayAccess $arr The array.
     * @param string|int $key The key.
     * @return bool true if the key exists and false otherwise.
     */
    public static function exists($arr, $key) {
        // If it's an object, index directly because array_key_exists doesn't
        // work with the ArrayAccess interface. Otherwise, check if it implements
        // ArrayAccess and fall back to array_key_exists.
        if(is_object($arr)) {
            return $arr->offsetExists($key);
        }
        if(is_array($arr)) {
            return array_key_exists($key, $arr);
        }

        return false;
    }

    /**
     * Extract values from an array with the given path.
     * @param array $arr
     * @param string|string[]|int $path
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

    /**
     * Generate a list of date-based indices.
     * @param string $format The index format.
     * @param string $interval The interval size (h,d,w,m,y).
     * @param int $from_ts Start timestamp.
     * @param int $to_ts End timestamp.
     * @return string[] List of indices.
     */
    public static function generateDateIndices($format, $interval, $from_ts, $to_ts) {
        $fmt_arr = [];
        $escaped = false;

        foreach(str_split($format) as $chr) {
            switch($chr) {
            case '[':
                $escaped = true;
                break;
            case ']':
                $escaped = false;
                break;
            default:
                $fmt_arr[] = $escaped ? "\\$chr":$chr;
                break;
            }
        }
        $fmt_str = implode('', $fmt_arr);

        $ret = [];
        $current = new \DateTime("@$from_ts");
        $to = new \DateTime("@$to_ts");

        $interval_map = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
        ];
        $interval_str = Util::get($interval_map, $interval, 'd');

        // Zero out the time component.
        $current->setTime($interval == 'h' ? $current->format('H'):0, 0);

        while ($current <= $to) {
            $ret[] = $current->format($fmt_str);
            $current = $current->modify("+1$interval_str");
        }

        return $ret;
    }
}
