<?php

declare(strict_types=1);

namespace Flying\HandlersList;

class ImmutableHandlersList extends HandlersList
{
    public function set(iterable $handlers): self
    {
        return new static($handlers, $this->getConstraint());
    }

    public function add(object $handler): self
    {
        return new static([...$this->toArray(), $handler], $this->getConstraint());
    }

    public function remove(object $handler): self
    {
        return new static(
            array_filter($this->toArray(), fn(object $h) => $h !== $handler),
            $this->getConstraint()
        );
    }

    public function clear(): self
    {
        return new static([], $this->getConstraint());
    }
}
