# gears-pipeline
The project that gears pipelines.

it runs a chain of handlers over iterables (iterators, generators, arrays).
the processing is memory efficient, no intermediate arrays are used.

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

## implemented handlers

- ``map(callable)``
- ``filter(callable)``
- ``reduce(callable, mixed = 0)``
- ``find(callable)``
- ``some(callable)``
- ``every(callable)``