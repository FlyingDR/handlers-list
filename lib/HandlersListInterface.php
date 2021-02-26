<?php

namespace Flying\HandlersList;

/**
 * Interface for generic list of handlers
 */
interface HandlersListInterface extends \Countable, \Iterator
{
    /**
     * Test if this handlers list will accept instances of given class
     *
     * @param object|string $class
     * @return bool
     */
    public function accepts($class): bool;

    /**
     * Checks if there is any handlers in list
     */
    public function isEmpty(): bool;

    /**
     * Checks whether given handler is available into list
     */
    public function contains(object $handler): bool;

    /**
     * Filter list of handlers using given test function
     *
     * @param callable $test Test function should accept object as single argument and return boolean
     * @return object[]
     */
    public function filter(callable $test): array;

    /**
     * Find handler by reducing list of available handlers using given test function
     *
     * @param callable $test Test function should accept object as single argument and return boolean
     * @return object|null
     */
    public function find(callable $test): ?object;

    /**
     * Replace current list of handlers with given handlers
     *
     * @param iterable<object> $handlers
     * @return static
     */
    public function set(iterable $handlers): self;

    /**
     * Add given handler to the list
     */
    public function add(object $handler): self;

    /**
     * Removes the specified handler from list
     */
    public function remove(object $handler): self;

    /**
     * Remove all handlers from the list
     */
    public function clear(): self;

    /**
     * Get interface constraint for handlers into list
     */
    public function getInterface(): ?string;

    /**
     * Get list of handlers as associative array
     */
    public function toArray(): array;
}
