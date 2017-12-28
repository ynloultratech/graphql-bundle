# Fields

Object Type is a collection of Fields and each field has its own type which allows building complex hierarchies.

## Defining Fields

To define a field inside a object must use the `@GraphQL\Field()`

````php
/**
 * @GraphQL\Field(type="string")
 */
protected $username;
````

> The `type` option is required unless the object is a doctrine entity and the field is a column or relation,
 in this case will be resolved automatically.
 
 Options:
 - **type**: The type of the field, read more about [Type System](type-system.md)
 - **name**: Name to expose the field, if not set will be automatically resolved.
 - **description**: Field description to expose in the documentation
 - **deprecationReason**: Mark the field as deprecated with the following reason
 - **options**: Options are used by [Definitions Extensions](extensions.md) to provide extra features
 

## Methods as Fields

Methods can be exposed as GraphQL fields, in these cases the `Field` annotation is always required.

````php
/**
 * @GraphQL\Field(type="bool")
 */
public function isAdmin(): bool
{
    return $this->getType() === self::TYPE_ADMIN;
}
````

Methods can have arguments to resolve the value based on given arguments

````php
/**
 * @GraphQL\Field(type="bool")
 * @GraphQL\Argument(name="type", type="string!")
 */
public function is($type): bool
{
    return $this->getType() === $type;
}
````
The above example allow the use of queries like this:

````graphql
query {
  users(first: 10) {
    login
    is(type: "ADMIN")
  }
}
````

## Fields Resolvers

Fields can be properties or methods inside any object,
but some times you need a more complex logic to resolve the value of the field. 
To accomplish this is required use field resolvers.

Field resolvers use [naming convention](../naming-conventions.md) like other [resolvers](../resolvers.md)
and should be created in:

`Query\{Node}\Field\{FieldName}`

To create a custom field called isCurrent for User node must create the following class.

````php
namespace AppBundle\Query\User\Field;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Ynlo\GraphQLBundle\Annotation as GraphQL;
use AppBundle\Entity\User;

/**
 * @GraphQL\Field(type="bool")
 */
class IsCurrent implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function __invoke(User $root): bool
    {
       ...
    }
}

````
The argument `$root` is automatically injected and contains the current Node. [Read more about arguments](../arguments.md)

## Override Field

@TODO

## Virtual Field

@TODO