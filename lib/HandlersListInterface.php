<?php

namespace Flying\HandlersList;

use Flying\HandlersList\Handler\HandlerInterface;

/**
 * Interface for object builder handlers lists
 */
interface HandlersListInterface extends \Countable, \Iterator
{
    /**
     * Checks if there is any handlers in list
     *
     * @return boolean
     */
    public function isEmpty(): bool;

    /**
     * Checks whether given handler is available into list
     *
     * @param HandlerInterface $handler
     * @return boolean
     */
    public function contains(HandlerInterface $handler): bool;

    /**
     * Filter list of handlers using given test function
     *
     * @param callable $test Test function should accept HandlerInterface as single argument and return boolean
     * @return HandlerInterface[]
     */
    public function filter(callable $test): array;

    /**
     * Find handler by reducing list of available handlers using given test function
     *
     * @param callable $test Test function should accept HandlerInterface as single argument and return boolean
     * @return HandlerInterface|null
     */
    public function find(callable $test): ?HandlerInterface;

    /**
     * @param HandlerInterface[] $handlers
     * @return HandlersListInterface
     */
    public function set(array $handlers): HandlersListInterface;

    /**
     * Add given handler to the list
     *
     * @param HandlerInterface $handler
     * @return HandlersListInterface
     */
    public function add(HandlerInterface $handler): HandlersListInterface;

    /**
     * Removes the specified handler from list
     *
     * @param HandlerInterface $handler
     * @return HandlersListInterface
     */
    public function remove(HandlerInterface $handler): HandlersListInterface;

    /**
     * Remove all handlers from the list
     *
     * @return HandlersListInterface
     */
    public function clear(): HandlersListInterface;

    /**
     * Get list of handlers as associative array
     *
     * @return array
     */
    public function toArray(): array;
}
