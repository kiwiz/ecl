<?php

class ParserTest extends PHPUnit_Framework_TestCase {
    /**
     * @dataProvider parseProvider
     */
    public function testParse($str) {
        $parser = new ECL\Parser;
        $parser->parse($str);
    }

    public function parseProvider() {
        return [
            [
                'set a=1; es:x a:b -c:d NOT e:f OR (g:h AND i:j) _exists_:k _missing_:l m:[0 TO 1] n:^x o:/x/ p:(x y z) q:$x.y |
                filter `true` | head 10 | tail 10 | map a=b -c | sort a,asc | join:inner x=y;'
            ],
            [
                'if `x` { load x; }'
            ]
        ];
    }
}
