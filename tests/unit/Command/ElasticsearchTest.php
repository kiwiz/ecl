<?php

class ElasticsearchTest extends PHPUnit_Framework_TestCase {
    private function getClientMock() {
        return $this->getMockBuilder('Elasticsearch\Client')
            ->disableOriginalConstructor()
            ->getMock();
    }
    public function testResolveFilter() {
        $table = new ECL\SymbolTable;
        $table['a'] = 1;
        $table['b'] = ['a', 'b'];

        $entry = [
            'index' => ECL\Command\Elasticsearch::LUT_INDEX,
            'type' => ECL\Command\Elasticsearch::LUT_TYPE,
            'path' => 'entries_string'
        ];

        $command = new ECL\Command\Elasticsearch;
        $mock_client = $this->getClientMock();
        $mock_client->expects($this->any())
            ->method('index')
            ->will($this->returnValue($entry));
        $command->setIndexClient($mock_client);

        $ret = Helper::invokeMethod($command, 'resolveFilter', [$table, ['a']]);
        $this->assertSame($ret, ['a']);

        $ret = Helper::invokeMethod($command, 'resolveFilter', [$table, [new ECL\Symbol('a')]]);
        $this->assertSame($ret, [1]);

        $ret = Helper::invokeMethod($command, 'resolveFilter', [$table, [new ECL\Symbol('b', ECL\Symbol::T_LIST)]]);
        unset($ret[0]['id']);
        $this->assertSame($ret, [$entry]);
    }
}
