Object Type is a collection of Fields and each field has its own type which allows building complex hierarchies.

# Defining Fields

To define a field inside a object must use the `@GraphQL\Field()`

````php
/**
 * @GraphQL\Field(type="string")
 */
protected $username;
````

>> The `type` option is required unless the object is a doctrine entity and the field is a column or relation,
 in this case will be resolved automatically.
 
 Options:
 - **type**: The type of the field, read more about [Type System](00_Type_System.md)
 - **name**: Name to expose the field, if not set will be automatically resolved.
 - **description**: Field description to expose in the documentation.
 - **deprecationReason**: Mark the field as deprecated with the following reason.
 - **complexity**: Customize field score complexity (**default** `children_complexity + 1`).
     Accepted values:
      * **numeric**: The field score will be calculated as `children_complexity + n`. 
      * **callable**: Custom static function (as string syntax) that accepts `$childrenComplexity` and `$args` 
      as arguments and it must return an integer value (score).
      * **expression**: Custom expression language. Context variables: `children_complexity` and the exposed in `$args`.
 - **maxConcurrentUsage**: *default: 0* How many times a field can be fetched in a query. Disabled by default. (see below) 
 - **options**: Options are used by [plugins](../07_Advanced/99_Definitions_Plugins.md) to provide extra features.
 

# Methods as Fields

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

# Fields Resolvers

Fields can be properties or methods inside any object,
but some times you need a more complex logic to resolve the value of the field. 
To accomplish this is required use field resolvers.

Field resolvers use [naming convention](../08_Reference/02_Naming_Conventions.md) like other [resolvers](../08_Reference/03_Resolvers.md)
and should be created in:

`Query\{Node}\Field\{FieldName}`

To create a custom field called isCurrent for User node must create the following class.

````php
namespace App\Query\User\Field;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Ynlo\GraphQLBundle\Annotation as GraphQL;
use App\Entity\User;

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
>> The argument `$root` is required, the type can be changed, anyway
   the field is created under the Node that belongs, and always receive current node instance as `root`argument.

Now you are able to execute a query like this:

````
query {
  users(first: 10) {
    login
    isCurrent
  }
}
````

# Max Concurrent Usage

Usage of field resolvers allow complex fields with any custom logic. 
With this scenario, sometimes is not a good idea allow to API consumers to request 
these complex fields more than **n** times per query.

Imagine a `User` object with a field called `hasValidEmail` 
*(this field execute a complex logic like DNS verification and other tasks)*.

````php
/**
 * @GraphQL\Field(type="bool")
 */
class hasValidEmail
{
    /**
     * @inheritDoc
     */
    public function __invoke(User $root)
    {
        $email = $root->getEmail();
        //check dns etc...
    }
}
````
Now, requesting this field in a query:
````
users {
    id
    username
    hasValidEmail
}
````
The usage of this field in a list has a high performance impact in the API, 
for each user in results, our system must execute all the field logic, including dns check etc.

To avoid this can set the `maxConcurrentUsage` of the field to **1**:

````php
 /**
  * @GraphQL\Field(type="bool", maxConcurrentUsage=1)
  */
 class hasValidEmail
````
With this change API consumers only can request this field on specific users *(1 time per query)*

```
node(id: 'VXNlcjox') {
    id
    username
    hasValidEmail
}
````
>> This option restrict API consumers to use some 
fields only on specific amount of records or one record, to avoid performance issues.

Now the following query will trow a error:

````
users {
    id
    username
    hasValidEmail
}
````

**The field \"hasValidEmail\" can be fetched only once per query. This field can`t be used in a list.**

# Override Field

@TODO

# Virtual Field

@TODO
