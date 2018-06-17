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
    private $handlers;
    /**
     * @var string
     */
    private $interface;
    /**
     * @var int
     */
    private $count = 0;
    /**
     * @var int
     */
    private $index = 0;

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
        $this->handlers = array_filter($this->handlers, function ($h) use ($handler) {
            return $h !== $handler;
        });
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
                $reflection = new \ReflectionClass($this->interface);
                $interface = $reflection->getShortName();
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
        usort($this->handlers, function ($a, $b) {
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
