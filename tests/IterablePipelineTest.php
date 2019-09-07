<?php

use Ailixter\Gears\PipelinedIteration;
use PHPUnit\Framework\TestCase;

class IterablePipelineTest extends TestCase
{

    public function testRepeat()
    {
        $it = PipelinedIteration::over(
            [1, 2],
            [3, 4, 5]
        );
        $it->byLongest();
        $it->map(function ($a, $b) {
            return $a + $b;
        });
        $it->map(function ($a) {
            return $a * $a;
        });
        $it->filter(function ($a) {
            return $a > 15;
        });
        $it->reduce(function ($r, $a) {
            $r[] = $a/2;
            return $r;
        }, []);
        $result = $it->getResult();
        $this->assertEquals($result, $it->getResult());
    }

    public function testReduce()
    {
        $result = PipelinedIteration::over(
            [1, 2],
            [3, 4],
            [5, 6],
            [7, 8]
        )
        ->map(function ($a, $b, $c, $d) {
            return [$a + $b, $c + $d];
        })
        ->reduce(function ($r, $x, $y) {
            return $r += $x * $y;
        })
        ->getResult();
        $this->assertEquals(132, $result);
    }

    public function testIterator()
    {
        $path = realpath(__dir__ . '/..');
        $this->assertDirectoryExists($path);

        $result = PipelinedIteration::over(
            new \DirectoryIterator($path)
        )
        ->filter(function (\DirectoryIterator $fileinfo) {
            return !$fileinfo->isDot();
        })
        ->map(function (\DirectoryIterator $fileinfo) {
            return (string)$fileinfo;
        })
        ->reduce(function ($r, $filename) {
            return $r .= "\t{$filename}\n";
        }, "Dir\n")
        ->getResult();
        $this->assertContains('tests', $result);
    }

}

