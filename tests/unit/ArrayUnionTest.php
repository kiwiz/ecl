<?php

class ArrayUnionTest extends PHPUnit_Framework_TestCase {
    public function testOffsetGet() {
        $au = new \ECL\ArrayUnion([
            ['a' => 1],
            ['a' => 2, 'b' => 3]
        ]);

        $this->assertSame(1, $au['a']);
        $this->assertSame(3, $au['b']);
    }

    public function testOffsetExists() {
        $au = new \ECL\ArrayUnion([
            ['a' => 1],
            ['b' => 2]
        ]);

        $this->assertTrue($au->offsetExists('a'));
        $this->assertTrue($au->offsetExists('b'));
        $this->assertFalse($au->offsetExists('c'));
    }
}
