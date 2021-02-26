<?php

declare(strict_types=1);

namespace Flying\HandlersList;

use Flying\HandlersList\Exception\InvalidHandlerException;
use Flying\HandlersList\Exception\InvalidHandlerInterfaceException;
use Flying\HandlersList\Handler\PrioritizedHandlerInterface;

class HandlersList implements HandlersListInterface
{
    /**
     * @var object[]
     */
    private array $handlers;
    private ?string $interface = null;

    /**
     * @param iterable<object> $handlers
     * @throws InvalidHandlerException
     */
    public function __construct(iterable $handlers = [], ?string $interface = null)
    {
        $this->setInterface($interface);
        $this->store($handlers);
        $this->update();
    }

    public function accepts($class): bool
    {
        if (\is_object($class)) {
            return $this->interface ? \is_a($class, $this->interface, true) : true;
        }

        if (\is_string($class) && \class_exists($class)) {
            if ($this->interface === null) {
                return true;
            }
            try {
                return (new \ReflectionClass($class))->implementsInterface($this->interface);
            } catch (\ReflectionException $e) {
                return false;
            }
        }

        return false;
    }

    public function isEmpty(): bool
    {
        return empty($this->handlers);
    }

    public function contains(object $handler): bool
    {
        return \in_array($handler, $this->handlers, true);
    }

    /**
     * @return object[]
     */
    public function filter(callable $test): array
    {
        return array_values(array_filter($this->handlers, $test));
    }

    /**
     * Find handler by reducing list of available handlers using given test function
     *
     * @param callable $test Test function should accept object as single argument and return boolean
     * @return object|null
     */
    public function find(callable $test): ?object
    {
        return array_reduce(
            $this->handlers,
            fn(?object $found, object $current) => $found ?? ($test($current) ? $current : null)
        );
    }

    public function set(iterable $handlers): self
    {
        $this->store($handlers);
        $this->update();
        return $this;
    }

    public function add(object $handler): self
    {
        if (!$this->contains($handler)) {
            $this->handlers[] = $this->validate($handler);
            $this->update();
        }
        return $this;
    }

    public function remove(object $handler): self
    {
        $this->handlers = array_filter($this->handlers, fn(object $h) => $h !== $handler);
        $this->update();
        return $this;
    }

    public function clear(): self
    {
        $this->handlers = [];
        $this->update();
        return $this;
    }

    public function getInterface(): ?string
    {
        return $this->interface;
    }

    public function toArray(): array
    {
        return $this->handlers;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->handlers);
    }

    public function count(): int
    {
        return \count($this->handlers);
    }

    protected function setInterface(?string $interface): void
    {
        if (\is_string($interface)) {
            try {
                new \ReflectionClass($interface);
            } catch (\ReflectionException $e) {
                throw new InvalidHandlerInterfaceException(sprintf('Given handler interface "%s" does not exists', $interface));
            }
        }

        $this->interface = $interface;
    }

    protected function store(iterable $handlers): void
    {
        /** @noinspection PhpParamsInspection */
        $this->handlers = array_map(function ($h) {
            return $this->validate($h);
        }, is_array($handlers) ? $handlers : iterator_to_array($handlers, false));
    }

    /**
     * @param mixed $handler
     * @return object
     * @throws InvalidHandlerException
     */
    protected function validate($handler): object
    {
        if (!is_object($handler)) {
            throw new InvalidHandlerException(sprintf('Handler should be an object, "%s" given instead', gettype($handler)));
        }

        if ($this->interface === null) {
            return $handler;
        }

        if (!is_a($handler, $this->interface)) {
            throw new InvalidHandlerException(sprintf('Handler "%s" should be instance of "%s"', get_class($handler), $this->interface));
        }

        return $handler;
    }

    /**
     * Update handlers list and related parameters after modification
     */
    protected function update(): void
    {
        usort($this->handlers, static function ($a, $b) {
            $ap = $a instanceof PrioritizedHandlerInterface ? $a->getHandlerPriority() : 0;
            $bp = $b instanceof PrioritizedHandlerInterface ? $b->getHandlerPriority() : 0;
            if ($ap > $bp) {
                return -1;
            }
            if ($ap < $bp) {
                return 1;
            }
            return 0;
        });
    }
}
