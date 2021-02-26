<?php

declare(strict_types=1);

namespace Flying\HandlersList\Tests;

use Flying\HandlersList\HandlersList;
use Flying\HandlersList\Tests\Fixtures\A;
use Flying\HandlersList\Tests\Fixtures\B;

class HandlersListTest extends BaseHandlersListTest
{
    public function testSetShouldReturnSelf(): void
    {
        $list = new HandlersList();
        $list2 = $list->set([new A()]);
        self::assertSame($list, $list2);
        self::assertEquals(1, $list2->count());
    }

    public function testAddShouldReturnSelf(): void
    {
        $list = new HandlersList();
        $list2 = $list->add(new A());
        self::assertSame($list, $list2);
        self::assertEquals(1, $list2->count());
    }

    public function testRemoveShouldReturnSelf(): void
    {
        $a = new A();
        $b = new B();
        $list = new HandlersList([$a, $b]);
        $list2 = $list->remove($a);
        self::assertSame($list, $list2);
        self::assertEquals(1, $list2->count());
    }

    public function testClearShouldReturnSelf(): void
    {
        $list = new HandlersList([new A(), new B()]);
        $list2 = $list->clear();
        self::assertSame($list, $list2);
        self::assertEquals(0, $list2->count());
    }
}
