<?php

namespace Flying\HandlersList\Tests\Fixtures;

use Flying\HandlersList\PrioritizedHandlerInterface;

class PrioritizedHandler implements PrioritizedHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getHandlerPriority(): int
    {
        return 0;
    }
}
