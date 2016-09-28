<?php

class FilterTest extends PHPUnit_Framework_TestCase {
    public function testFilter() {
        $command = new \ECL\Command\Filter('_["a"] == 1');
        $res = $command->process(Helper::getSymbolTable([['a' => 1], ['a' => 99]]));

        $expected = [
            ['a' => 1]
        ];
        $this->assertSame($expected, $res->getAll());
    }
}
