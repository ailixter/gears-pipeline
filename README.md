# gears-pipeline
The project that gears pipelines.

```php
    echo PipelinedIteration::over(
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
    ->getResult(); // 132
```