<?php

namespace Flying\HandlersList;

use Flying\HandlersList\Exception\InvalidHandlerException;
use Flying\HandlersList\Handler\HandlerInterface;
use Flying\HandlersList\Handler\PrioritizedHandlerInterface;

class HandlersList implements HandlersListInterface
{
    /**
     * @var HandlerInterface[]
     */
    private array $handlers;
    /**
     * @var string
     */
    private string $interface;
    /**
     * @var int
     */
    private int $count = 0;
    /**
     * @var int
     */
    private int $index = 0;

    /**
     * @param HandlerInterface[] $handlers
     * @param string|null $interface
     * @throws InvalidHandlerException
     */
    public function __construct(array $handlers = [], ?string $interface = HandlerInterface::class)
    {
        $this->interface = $interface ?? HandlerInterface::class;
        $this->set($handlers);
    }

    /**
     * Test if this handlers list will accept instances of given class
     *
     * @param object|string $class
     * @return bool
     */
    public function accepts($class): bool
    {
        if (\is_object($class)) {
            return \is_a($class, $this->interface, true);
        }
        if (\is_string($class)) {
            try {
                return (new \ReflectionClass($class))->implementsInterface($this->interface);
            } catch (\ReflectionException $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * Checks if there is any handlers in list
     *
     * @return boolean
     */
    public function isEmpty(): bool
    {
        return empty($this->handlers);
    }

    /**
     * Checks whether given handler is available into list
     *
     * @param HandlerInterface $handler
     * @return boolean
     */
    public function contains(HandlerInterface $handler): bool
    {
        return \in_array($handler, $this->handlers, true);
    }

    /**
     * Filter list of handlers using given test function
     *
     * @param callable $test Test function should accept HandlerInterface as single argument and return boolean
     * @return HandlerInterface[]
     */
    public function filter(callable $test): array
    {
        return array_values(array_filter($this->handlers, $test));
    }

    /**
     * Find handler by reducing list of available handlers using given test function
     *
     * @param callable $test Test function should accept HandlerInterface as single argument and return boolean
     * @return HandlerInterface|null
     */
    public function find(callable $test): ?HandlerInterface
    {
        return array_reduce(
            $this->handlers,
            fn(?HandlerInterface $found, HandlerInterface $current) => $found ?? ($test($current) ? $current : null)
        );
    }

    /**
     * @param HandlerInterface[] $handlers
     * @return HandlersListInterface
     */
    public function set(array $handlers): HandlersListInterface
    {
        $this->handlers = array_map(function ($h) {
            return $this->validate($h);
        }, $handlers);
        $this->update();
        return $this;
    }

    /**
     * Add given handler to the list
     *
     * @param HandlerInterface $handler
     * @return HandlersListInterface
     * @throws InvalidHandlerException
     */
    public function add(HandlerInterface $handler): HandlersListInterface
    {
        if (!$this->contains($handler)) {
            $this->handlers[] = $this->validate($handler);
            $this->update();
        }
        return $this;
    }

    /**
     * Removes the specified handler from list
     *
     * @param HandlerInterface $handler
     * @return HandlersListInterface
     */
    public function remove(HandlerInterface $handler): HandlersListInterface
    {
        $this->handlers = array_filter($this->handlers, fn(HandlerInterface $h) => $h !== $handler);
        $this->update();
        return $this;
    }

    /**
     * Remove all handlers from the list
     *
     * @return HandlersListInterface
     */
    public function clear(): HandlersListInterface
    {
        $this->handlers = [];
        $this->update();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->handlers;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return \count($this->handlers);
    }

    /**
     * {@inheritdoc}
     * @return HandlerInterface
     */
    public function current(): HandlerInterface
    {
        return $this->handlers[$this->index];
    }

    /**
     * {@inheritdoc}
     * @return int
     */
    public function key(): int
    {
        return $this->index;
    }

    /**
     * {@inheritdoc}
     * @return void
     */
    public function next(): void
    {
        $this->index++;
    }

    /**
     * {@inheritdoc}
     * @return boolean
     */
    public function valid(): bool
    {
        return $this->index < $this->count;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->index = 0;
    }

    /**
     * @param mixed $handler
     * @return HandlerInterface
     * @throws InvalidHandlerException
     */
    protected function validate($handler): HandlerInterface
    {
        if (!\is_object($handler) || !is_subclass_of($handler, $this->interface)) {
            $interface = $this->interface;
            try {
                $interface = (new \ReflectionClass($this->interface))->getShortName();
            } catch (\ReflectionException $e) {

            }
            throw new InvalidHandlerException(sprintf('Handler should implement "%s" interface', $interface));
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
        $this->count = $this->count();
        $this->index = 0;
    }
}
