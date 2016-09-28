<?php

class HeadTest extends PHPUnit_Framework_TestCase {
    public function testHeadEmpty() {
        $command = new \ECL\Command\Head(1);
        $res = $command->process(Helper::getSymbolTable([]));

        $expected = [];
        $this->assertSame($expected, $res->getAll());
    }

    public function testHeadZero() {
        $command = new \ECL\Command\Head(0);
        $res = $command->process(Helper::getSymbolTable([[1]]));

        $expected = [];
        $this->assertSame($expected, $res->getAll());
    }


    public function testHeadOne() {
        $command = new \ECL\Command\Head(1);
        $res = $command->process(Helper::getSymbolTable([[1], [2]]));

        $expected = [
            [1]
        ];
        $this->assertSame($expected, $res->getAll());
    }
}
