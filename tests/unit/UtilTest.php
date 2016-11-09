<?php

class UtilTest extends PHPUnit_Framework_TestCase {
    public function getProvider() {
        return [
            [[], null, null, null],
            [[1], 0, null, 1],
            [[1], 1, null, null],
            [['a' => 1], 'a', null, 1],
            [['a' => 1], 'b', null, null],
        ];
    }

    /**
     * @dataProvider getProvider
     */
    public function testGet($arr, $key, $default, $expected) {
        $this->assertSame(ECL\Util::get($arr, $key, $default), $expected);
    }

    public function existsProvider() {
        return [
            [[], null, false],
            [[1], 0, true],
            [[1], 1, false],
            [['a' => 1], 'a', true],
            [['a' => 1], 'b', false],
        ];
    }

    /**
     * @dataProvider existsProvider
     */
    public function testExists($arr, $key, $expected) {
        $this->assertSame(ECL\Util::exists($arr, $key), $expected);
    }

    public function pluckProvider() {
        return [
            [[['a' => 'x'], ['a' => 'y'], ['b' => 'z']], 'a', ['x', 'y']],
            [[], 'a', []]
        ];
    }

    /**
     * @dataProvider pluckProvider
     */
    public function testPluck($arr, $key, $expected) {
        $this->assertSame(ECL\Util::pluck($arr, $key), $expected);
    }

    public function generateDateIndicesProvider() {
        return [
            ['[abc-]Y', 'y', 1451606400, 1483228800, ['abc-2016', 'abc-2017']],
            ['[abc-]m', 'm', 1451606400, 1454284800, ['abc-01', 'abc-02']],
            ['[abc-]W', 'w', 1451606400, 1452211200, ['abc-53', 'abc-01']],
            ['[abc-]Y.m.d', 'd', 1451606400, 1451692800, ['abc-2016.01.01', 'abc-2016.01.02']],
            ['[abc-]H', 'h', 1451606400, 1451613600, ['abc-00', 'abc-01', 'abc-02']],
        ];
    }

    /**
     * @dataProvider generateDateIndicesProvider
     */
    public function testGenerateDateIndices($format, $interval, $from_ts, $to_ts, $expected) {
        $this->assertSame(ECL\Util::generateDateIndices($format, $interval, $from_ts, $to_ts), $expected);
    }
}
