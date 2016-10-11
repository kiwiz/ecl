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

    public function getIndicesProvider() {
        return [
            ['X', 946771200, 946771200, ['X-2000.01.02']],
            ['X', 946771199, 946771200, ['X-2000.01.01', 'X-2000.01.02']],

        ];
    }

    /**
     * @dataProvider getIndicesProvider
     */
    public function testGetIndices($index, $from_ts, $to_ts, $expected) {
        $this->assertSame(ECL\Util::getIndices($index, $from_ts, $to_ts), $expected);
    }
}
