<?php

use Ailixter\Gears\PipelinedKeyIteration;
use PHPUnit\Framework\TestCase;

class IterableKeysPipelineTest extends TestCase
{

    public function testRepeat()
    {
        $it = PipelinedKeyIteration::over(
            ['a' => 1, 'b' => 2],
            ['c' => 3, 'd' => 4, 'e' => 5]
        );
        $it->byLongest();
        $it->map(function ($a, $b) {
            return [[$a[0].$b[0] => $a[1] + $b[1]]];
        });
        $it->filter(function ($a) {
            return current($a) > 1;
        });
        $it->reduce(function ($r, $a) {
            $r[key($a)] = current($a)/2;
            return $r;
        }, []);
        $result = $it->getResult();
        $this->assertEquals($result, $it->getResult());
    }
}