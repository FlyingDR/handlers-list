# Generic list of arbitrary handlers

This very simple library provides implementation of generic list of arbitrary handlers. 

Its primary goal is to simplify process of organizing multiple related objects into iterable list of handlers with: 

1. Ensuring type correctness by providing class constraint, handlers need to implement / extend
2. Support for defining handlers priority

### Requirements

No dependencies are required, just PHP 7.4 or 8.x.

### Example

```php
interface MyHandler {
    public function doSomething(): void;
}

class Foo implements MyHandler {

}

class Bar implements MyHandler {

}

class Baz extends Bar {

}

// Creating list of handlers
$handlers = new HandlersList([
    new Foo(), 
    new Bar(),
    new Baz(),
], MyHandler::class);

// ... later in code ...
foreach($handlers as $handler) {
    // We can be sure that $handler is of type MyHandler::class
    $handler->doSomething(); 
}
```

In a case if some handler implements `PrioritizedHandlerInterface` - its priority is considered:

```php
interface MyHandler {
    public function name(): string;
}

class A implements MyHandler {
    public function name(): string {
        return 'A';
    }
}

class B implements MyHandler, PrioritizedHandlerInterface {
    public function name(): string {
        return 'B';
    }

    public function getHandlerPriority(): int {
        return 10;
    }
}
 
$handlers = new HandlersList([
    new A(), 
    new B(),
], MyHandler::class);

foreach($handlers as $handler) {
    echo $handler->name() . ' '; 
}
```

Example above will output `B A` because `B` have higher priority then `A` despite the fact that it was put later in the list of handlers. 
     
### Methods

It is, of course, possible to modify list of handlers:
 - `set()` - set new list of handlers
 - `add()` - add a new handler to the list
 - `remove()` - remove given handler from the list
 - `clear()` - remove all handlers from the list

There is also several methods for inspecting list of handlers:
 - `isEmpty()` - check if handlers list is empty
 - `count()` - get number of handlers in the list
 - `accepts()` - check if list accepts a given object or objects of given cass  
 - `contains()` - check if given handler is available in the list
 - `filter()` - filter handlers list using provided test callable and return array of matching handlers
 - `find()` - searches for a handler using provided callable
 - `getIterator()` - get handlers from the list as iterator
 - `toArray()` - get handlers from the list as array

And remaining methods:
 - `getConstraint()` - get class constraint that is applied to handlers in this list  

### Immutable handlers list

Besides default, mutable implementation of handlers list there is also immutable version: `ImmutableHandlersList`. Its functionality is completely same in except of list modification methods that returns new copy of the list instead of modifying original one.

### License

MIT License
