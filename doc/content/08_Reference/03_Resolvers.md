# ADR vs MVC

The [MVC Pattern](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) it's OK, 
but APIs does not have a view, and why should have controllers?

- Controllers tend to have a lot of dependencies and stuff that is not really related to each other.
- Controllers many times violates the Single Responsibility Principle (SRP).
- Action classes tend to be rather small, typically less than 100 loc for us.
- It is easier to search for a class name than a method name in most IDEs.
- Action classes depend on what they really needed.
- Action classes in contrast to controller classes can be reusable.
- Action classes come in pretty handy as "common logic" can be defined in the parent class or traits.

> First of all I do have the feeling that controller classes make it harder to structure your logic. 
I have seen a lot of "God Controllers" that do a shitload of stuff. 
Stuff that is not really related to each other.
I assume this happened because it seems to be easier to add a new method to an existing 
controller than to add a new class, think about how to name the class properly 
and in which namespace to put it. [Controller classes vs. Action classes](https://blog.bitexpert.de/blog/controller-classes-vs.-action-classes)

After the above conclusions the GraphQLBundle use [ADR Pattern](http://pmjones.io/adr/), 
and actions are called `Resolvers`.


# Resolvers

GraphQLBundle use resolvers for many operations, queries, mutations, fetch field values etc. 
A valid resolver is any [invokable class]().

````php
class ResolveSomething
{
    public function __invoke()
    {
        // TODO: Implement __invoke() method.
    }
}
````

>>> As of Symfony 4.0 if your resolver is registered as service, must be `public`, otherwise
the API can't find your resolver service and use the standalone class instead.
In symfony 3.4 is not a requirement, but is recommended otherwise a deprecated warning is triggered.
[More details here.](https://symfony.com/blog/new-in-symfony-3-4-services-are-private-by-default)

The following example register all resolvers as services and set all **PUBLIC**:

````yaml
services:
    App\Query\:
        resource: '../src/Query/*'
        public: true

    App\Mutation\:
        resource: '../src/Mutation/*'
        public: true
````
## Resolver Types

@TODO