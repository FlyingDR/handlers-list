<?php

namespace Flying\HandlersList\Handler;

/**
 * Interface for handlers that needs to define their priority among other handlers
 */
interface PrioritizedHandlerInterface extends HandlerInterface
{
    /**
     * Get priority of this handler
     * Generic handlers should have low priority,
     * more specific handlers should have higher priority
     *
     * @return int
     */
    public function getHandlerPriority(): int;
}
