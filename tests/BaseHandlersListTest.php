<?php

namespace Flying\HandlersList\Tests;

use Flying\HandlersList\Exception\InvalidHandlerException;
use Flying\HandlersList\Exception\InvalidHandlerInterfaceException;
use Flying\HandlersList\HandlersList;
use Flying\HandlersList\Tests\Fixtures\A;
use Flying\HandlersList\Tests\Fixtures\AInterface;
use Flying\HandlersList\Tests\Fixtures\B;
use Flying\HandlersList\Tests\Fixtures\BInterface;
use Flying\HandlersList\Tests\Fixtures\C;
use Flying\HandlersList\Tests\Fixtures\D;
use Flying\HandlersList\Tests\Fixtures\PrioritizedHandler;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

abstract class BaseHandlersListTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider dpIterables
     */
    public function testAllowPassingIterablesIntoConstructor(iterable $items): void
    {
        $this->expectNotToPerformAssertions();
        new HandlersList($items);
    }

    public function dpIterables(): array
    {
        return [
            [
                [],
            ],
            [
                [new A()],
            ],
            [
                [new A(), new B()],
            ],
            [
                new \ArrayIterator([new A(), new B()]),
            ],
            [
                (static function (): \Generator {
                    yield new A();
                    yield new B();
                })(),
            ],
        ];
    }

    /**
     * @dataProvider dpItems
     */
    public function testItemsPassedInConstructorShouldBeValidatedAgainstInterface(array $items, ?string $interface, bool $shouldFail): void
    {
        if ($shouldFail) {
            $this->expectException(InvalidHandlerException::class);
        }
        $list = new HandlersList($items, $interface);
        self::assertSame($items, $list->toArray());
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
                [new A(), new D()],
                A::class,
                false,
            ],
            [
                [true, false, 123, 'abc'],
                null,
                true,
            ],
        ];
    }

    public function testInvalidInterfaceShouldNotBeAccepted(): void
    {
        $this->expectException(InvalidHandlerInterfaceException::class);
        new HandlersList([new A()], 'unavailable interface');
    }

    /**
     * @param string|null $interface
     * @param string|object|null $class
     * @param bool $acceptable
     * @dataProvider acceptanceItems
     */
    public function testClassAcceptance(?string $interface, $class, bool $acceptable): void
    {
        $list = new HandlersList([], $interface);
        self::assertEquals($acceptable, $list->accepts($class));
    }

    public function acceptanceItems(): array
    {
        return [
            [
                AInterface::class,
                A::class,
                true,
            ],
            [
                BInterface::class,
                B::class,
                true,
            ],
            [
                AInterface::class,
                B::class,
                false,
            ],
            [
                null,
                A::class,
                true,
            ],
            [
                null,
                B::class,
                true,
            ],
            [
                AInterface::class,
                new A(),
                true,
            ],
            [
                AInterface::class,
                new B(),
                false,
            ],
            [
                A::class,
                new A(),
                true,
            ],
            [
                A::class,
                new D(),
                true,
            ],
            [
                null,
                new A(),
                true,
            ],
            [
                null,
                null,
                false,
            ],
            [
                null,
                true,
                false,
            ],
            [
                null,
                [],
                false,
            ],
            [
                null,
                42,
                false,
            ],
            [
                null,
                'test',
                false,
            ],
        ];
    }

    public function testListManipulations(): void
    {
        $list = new HandlersList();
        self::assertTrue($list->isEmpty());
        self::assertEquals(0, $list->count());
        self::assertEquals([], $list->toArray());

        $a = new A();
        $b = new B();
        $items = [$a, $b];
        $list->set($items);
        self::assertFalse($list->isEmpty());
        self::assertEquals(2, $list->count());
        self::assertEquals($items, $list->toArray());

        $c = new C();
        self::assertTrue($list->contains($items[0]));
        self::assertFalse($list->contains($c));

        $list->remove($c);
        self::assertEquals(2, $list->count());
        self::assertTrue($list->contains($items[0]));
        self::assertTrue($list->contains($items[1]));

        $list->add($c);
        self::assertEquals(3, $list->count());
        self::assertTrue($list->contains($c));

        $list->remove($items[0]);
        self::assertEquals(2, $list->count());
        self::assertFalse($list->contains($items[0]));

        $list->clear();
        self::assertTrue($list->isEmpty());
        self::assertEquals(0, $list->count());
        self::assertEquals([], $list->toArray());

        $list->set([$a, $b, $c]);
        $test1 = static fn($h) => $h instanceof A;
        $test2 = static fn($h) => $h instanceof B || $h instanceof C;
        $test3 = static fn() => true;
        $test4 = static fn() => false;
        self::assertEquals([$a], $list->filter($test1));
        self::assertEquals([$b, $c], $list->filter($test2));
        self::assertEquals([$a, $b, $c], $list->filter($test3));
        self::assertEquals([], $list->filter($test4));

        self::assertEquals($a, $list->find($test1));
        self::assertEquals($b, $list->find($test2));
        self::assertEquals($a, $list->find($test3));
        self::assertEquals(null, $list->find($test4));
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
            self::assertSame($items[$index++], $item);
        }
    }

    public function testListItemsAreSortedByPriorityIfPossible(): void
    {
        $h1 = $this->prophesize(PrioritizedHandler::class);
        $h1
            ->getHandlerPriority()
            ->shouldBeCalled()
            ->willReturn(0);
        $h2 = $this->prophesize(PrioritizedHandler::class);
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
        self::assertSame($expected, $list->toArray());

        $index = 0;
        foreach ($list as $item) {
            self::assertSame($expected[$index++], $item);
        }
    }

    public function testListItemsAreUnique(): void
    {
        $item = new A();
        $list = new HandlersList();
        $list->add($item);
        $list->add($item);
        $list->add($item);

        self::assertFalse($list->isEmpty());
        self::assertEquals(1, $list->count());
        self::assertTrue($list->contains($item));
        self::assertSame($item, $list->toArray()[0]);
    }

    public function testInterfaceConstraintRetrieval() : void
    {
        $list = new HandlersList();
        self::assertNull($list->getInterface());

        $list = new HandlersList([], A::class);
        self::assertSame(A::class, $list->getInterface());
    }
}
