<?php

declare(strict_types=1);

namespace Flying\HandlersList\Tests;

use Flying\HandlersList\ImmutableHandlersList;
use Flying\HandlersList\Tests\Fixtures\A;
use Flying\HandlersList\Tests\Fixtures\B;
use Flying\HandlersList\Tests\Fixtures\C;

class ImmutableBaseHandlersListTest extends BaseHandlersListTest
{
    public function testSetShouldBeImmutable(): void
    {
        $a = new A();
        $b = new B();
        $c = new C();
        $list = new ImmutableHandlersList([$a, $b]);
        $list2 = $list->set([$c]);

        self::assertNotSame($list, $list2);
        self::assertEquals(2, $list->count());
        self::assertEquals(1, $list2->count());
        self::assertFalse($list2->contains($a));
        self::assertFalse($list2->contains($b));
        self::assertTrue($list2->contains($c));
    }

    public function testAddShouldBeImmutable(): void
    {
        $a = new A();
        $b = new B();
        $c = new C();
        $list = new ImmutableHandlersList([$a, $b]);
        $list2 = $list->add($c);

        self::assertNotSame($list, $list2);
        self::assertEquals(2, $list->count());
        self::assertEquals(3, $list2->count());
        self::assertTrue($list2->contains($a));
        self::assertTrue($list2->contains($b));
        self::assertTrue($list2->contains($c));
    }

    public function testRemoveShouldBeImmutable(): void
    {
        $a = new A();
        $b = new B();
        $c = new C();
        $list = new ImmutableHandlersList([$a, $b, $c]);
        $list2 = $list->remove($c);

        self::assertNotSame($list, $list2);
        self::assertEquals(3, $list->count());
        self::assertEquals(2, $list2->count());
        self::assertTrue($list2->contains($a));
        self::assertTrue($list2->contains($b));
        self::assertFalse($list2->contains($c));
    }

    public function testClearShouldBeImmutable(): void
    {
        $a = new A();
        $b = new B();
        $list = new ImmutableHandlersList([$a, $b]);
        $list2 = $list->clear();

        self::assertNotSame($list, $list2);
        self::assertEquals(2, $list->count());
        self::assertEquals(0, $list2->count());
        self::assertFalse($list2->contains($a));
        self::assertFalse($list2->contains($b));
    }
}
