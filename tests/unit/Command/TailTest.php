<?php

class TailTest extends PHPUnit_Framework_TestCase {
    public function testTailEmpty() {
        $command = new \ECL\Command\Tail(1);
        $res = $command->process(Helper::getSymbolTable([]));

        $expected = [];
        $this->assertSame($expected, $res->getAll());
    }

    public function testTailZero() {
        $command = new \ECL\Command\Tail(0);
        $res = $command->process(Helper::getSymbolTable([[1]]));

        $expected = [];
        $this->assertSame($expected, $res->getAll());
    }


    public function testTailOne() {
        $command = new \ECL\Command\Tail(1);
        $res = $command->process(Helper::getSymbolTable([[1], [2]]));

        $expected = [
            [2]
        ];
        $this->assertSame($expected, $res->getAll());
    }
}
