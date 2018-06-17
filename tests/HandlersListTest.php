<?php

namespace Flying\HandlersList\Tests\Registry;

use Flying\HandlersList\Exception\InvalidHandlerException;
use Flying\HandlersList\HandlersList;
use Flying\HandlersList\Tests\Fixtures\A;
use Flying\HandlersList\Tests\Fixtures\AInterface;
use Flying\HandlersList\Tests\Fixtures\B;
use Flying\HandlersList\Tests\Fixtures\BInterface;
use Flying\HandlersList\Tests\Fixtures\C;
use Flying\HandlersList\Tests\Fixtures\PrioritizedHandler;
use PHPUnit\Framework\TestCase;

class HandlersListTest extends TestCase
{
    /**
     * @param array $items
     * @param string $interface
     * @param boolean $shouldFail
     * @dataProvider dpItems
     */
    public function testItemsPassedInConstructorShouldBeValidatedAgainstInterface($items, $interface, $shouldFail): void
    {
        if ($shouldFail) {
            $this->expectException(InvalidHandlerException::class);
        }
        $list = new HandlersList($items, $interface);
        $this->assertSame($items, $list->toArray());
    }

    public function dpItems(): array
    {
        return [
            [
                [new A()],
                AInterface::class,
                false,
            ],
            [
                [new B()],
                BInterface::class,
                false,
            ],
            [
                [new B()],
                AInterface::class,
                true,
            ],
            [
                [new A(), new B()],
                null,
                false,
            ],
            [
                [new A(), new B()],
                AInterface::class,
                true,
            ],
            [
                [true, false, 123, 'abc'],
                null,
                true,
            ],
        ];
    }

    public function testListManipulations(): void
    {
        $list = new HandlersList();
        $this->assertTrue($list->isEmpty());
        $this->assertEquals(0, $list->count());
        $this->assertEquals([], $list->toArray());

        $a = new A();
        $b = new B();
        $items = [$a, $b];
        $list->set($items);
        $this->assertFalse($list->isEmpty());
        $this->assertEquals(2, $list->count());
        $this->assertEquals($items, $list->toArray());

        $c = new C();
        $this->assertTrue($list->contains($items[0]));
        $this->assertFalse($list->contains($c));

        $list->remove($c);
        $this->assertEquals(2, $list->count());
        $this->assertTrue($list->contains($items[0]));
        $this->assertTrue($list->contains($items[1]));

        $list->add($c);
        $this->assertEquals(3, $list->count());
        $this->assertTrue($list->contains($c));

        $list->remove($items[0]);
        $this->assertEquals(2, $list->count());
        $this->assertFalse($list->contains($items[0]));

        $list->clear();
        $this->assertTrue($list->isEmpty());
        $this->assertEquals(0, $list->count());
        $this->assertEquals([], $list->toArray());

        $list->set([$a, $b, $c]);
        $test1 = function ($h) {
            return $h instanceof A;
        };
        $test2 = function ($h) {
            return $h instanceof B || $h instanceof C;
        };
        $test3 = function () {
            return true;
        };
        $test4 = function () {
            return false;
        };
        $this->assertEquals([$a], $list->filter($test1));
        $this->assertEquals([$b, $c], $list->filter($test2));
        $this->assertEquals([$a, $b, $c], $list->filter($test3));
        $this->assertEquals([], $list->filter($test4));

        $this->assertEquals($a, $list->find($test1));
        $this->assertEquals($b, $list->find($test2));
        $this->assertEquals($a, $list->find($test3));
        $this->assertEquals(null, $list->find($test4));
    }

    public function testListIsIterable(): void
    {
        $items = [
            new A(),
            new B(),
            new C(),
        ];
        $list = new HandlersList($items);
        $index = 0;
        foreach ($list as $item) {
            $this->assertSame($items[$index++], $item);
        }
    }

    public function testListItemsAreSortedByPriorityIfPossible(): void
    {
        $h1 = $this->prophesize(PrioritizedHandler::class);
        /** @noinspection PhpUndefinedMethodInspection */
        $h1
            ->getHandlerPriority()
            ->shouldBeCalled()
            ->willReturn(0);
        $h2 = $this->prophesize(PrioritizedHandler::class);
        /** @noinspection PhpUndefinedMethodInspection */
        $h2
            ->getHandlerPriority()
            ->shouldBeCalled()
            ->willReturn(10);

        $list = new HandlersList([
            $h1->reveal(),
            $h2->reveal(),
        ], PrioritizedHandler::class);
        $expected = [
            $h2->reveal(),
            $h1->reveal(),
        ];
        $this->assertSame($expected, $list->toArray());

        $index = 0;
        foreach ($list as $item) {
            $this->assertSame($expected[$index++], $item);
        }
    }

    public function testListItemsAreUnique(): void
    {
        $item = new A();
        $list = new HandlersList();
        $list->add($item);
        $list->add($item);
        $list->add($item);

        $this->assertFalse($list->isEmpty());
        $this->assertEquals(1, $list->count());
        $this->assertTrue($list->contains($item));
        $this->assertSame($item, $list->toArray()[0]);
    }
}
