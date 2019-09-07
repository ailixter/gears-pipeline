<?php
/*
 * TODO Description
 * (C) 2019, AII (Alexey Ilyin)
 */

namespace Ailixter\Gears;

class PipelinedIteration
{
    public function __construct(iterable ...$args) {
        foreach ($args as $data) {
            $this->addData($data);
        }
    }

    public static function over(iterable ...$args)
    {
        return new static(...$args);
    }

    /**
     * @var iterable[]
     */
    private $data = [];

    public function addData(iterable $data): self
    {
        $this->data[] = $data;
        return $this;
    }

    /**
     * @var Closure[]
     */
    private $handlers = [];
    private $handlersFinished;

    protected function addHandler(callable $handler, $final = false): self
    {
        if ($this->handlersFinished) {
            throw new \RuntimeException("handlers chain finished by {$this->handlersFinished}");
        }
        if ($final) {
            $this->handlersFinished = $final;
        }
        $this->handlers[] = $handler;
        return $this;
    }

    public function map(callable $fn): self
    {
        return $this->addHandler(static function (array $args) use ($fn): array {
            return [$fn(...$args), false];
        });
    }

    public function filter(callable $fn = null): self
    {
        if (!$fn) {
            $fn = [$this, 'isTruish'];
        }
        return $this->addHandler(static function (array $args) use ($fn): array {
            if (!$fn(...$args)) {
                return [null, self::CONTINUE];
            }
            if (count($args) === 1) {
                return [$args[0], false];
            }
            return [$args, false];
        });
    }

    protected function isTruish(...$args)
    {
        foreach ($args as $arg) {
            if (!$arg) return false;
        }
        return true;
    }

    public function reduce(callable $fn, $initial = 0): self
    {
        $this->initResult($initial);
        return $this->addHandler(function (array $args) use ($fn): array {
            $this->result = $fn($this->result, ...$args);
            return [null, self::CONTINUE];
        }, __function__);
    }

    public function find(callable $fn): self
    {
        return $this->addHandler(function (array $args) use ($fn): array {
            if ($fn(...$args)) {
                $this->result = count($args) === 1 ? $args[0] : $args;
                return [null, self::BREAK];
            }
            return [null, self::CONTINUE];
        }, __function__);
    }

    public function some(callable $fn): self
    {
        $this->initResult(false);
        return $this->addHandler(function (array $args) use ($fn): array {
            if ($fn(...$args)) {
                $this->result = true;
                return [null, self::BREAK];
            }
            return [null, self::CONTINUE];
        }, __function__);
    }

    public function every(callable $fn): self
    {
        $this->initResult(true);
        return $this->addHandler(function (array $args) use ($fn): array {
            if (!$fn(...$args)) {
                $this->result = false;
                return [null, self::BREAK];
            }
            return [null, self::CONTINUE];
        }, __function__);
    }

    private $initial = [];
    private $result;

    /**
     * Set initial value of result.
     * @return  self
     */
    public function initResult($initial): self
    {
        $this->initial = $initial;
        return $this;
    }

    private const CONTINUE = 1;
    private const BREAK = 2;

    public function getResult()
    {
        $this->result = $this->initial;
        $iterators = $this->dataIterators();
        while ($args = $this->itemsRow($iterators)) {
            foreach ($this->handlers as $handle) {
                [$return, $do] = $handle($args);
                switch ($do) {
                    case self::CONTINUE: continue 3;
                    case self::BREAK: break 3;
                }
                $args = is_array($return) ? $return : [$return];
            }
            $this->appendToResult($return);
        }
        $result = $this->result;
        unset($this->result);
        return $result;
    }

    protected function appendToResult($return): void
    {
        $this->result[] = $return;
    }

    private function dataIterators()
    {
        $generators = [];
        foreach ($this->data as $data) {
            $generators[] = (static function () use ($data) {
                yield from $data;
            })();
        }
        return $generators;
    }

    private function itemsRow(array $iterators): ?array
    {
        $items = [];
        $nulls = count($iterators);
        foreach ($iterators as $data) {
            if ($data->valid()) {
                $items[] = $data->current();
                $data->next();
                continue;
            }
            $nulls--;
            if ($this->greedy && $nulls > 0) {
                $items[] = null;
                continue;
            }
            return null;
        }
        return $items;
    }

    private $greedy = false;

    public function byShortest(): self
    {
        $this->greedy = false;
        return $this;
    }

    public function byLongest(): self
    {
        $this->greedy = true;
        return $this;
    }
}
