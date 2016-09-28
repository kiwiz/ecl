<?php

class CountTest extends PHPUnit_Framework_TestCase {
    public function testCountOne() {
        $command = new \ECL\Command\Count;
        $res = $command->process(Helper::getSymbolTable([[]]));

        $expected = [
            'count' => 1
        ];
        $this->assertSame($expected, $res[0]);
    }

    public function testCountZero() {
        $command = new \ECL\Command\Count;
        $res = $command->process(Helper::getSymbolTable([]));

        $expected = [
            'count' => 0
        ];
        $this->assertSame($expected, $res[0]);
    }
}
