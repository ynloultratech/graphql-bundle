Basic CRUD operations are very helpful, but many times is not enough.

# Override Resolvers

What happen if you need a custom way to add the record to the database?

In these scenarios you must override the default resolver used to execute the operation.
To override the resolver create a new one using [naming convention](../08_Reference/02_Naming_Conventions.md).

> GraphQL Bundle works with [Resolvers](../08_Reference/03_Resolvers.md) and each resolver 
is responsible for executing one task and only one task.

Mutations Resolvers:
 
 `Mutation\{Node}\{OperationName}`
 
Then, if you need override the action `addUser` must create the following class:

````php
namespace App\Mutation\User;

class AddUser
{
    public function __invoke($input)
    {
       ...
    }
}
````

See the `$input` parameter in the `__invoke` action, 
the name of each parameter should match with the name of 
each argument in the GraphQL mutation schema.

The input argument contains the data *(array)* entered by the user in the input argument. 
At this point you can put your own logic in the resolver to do anything what you need. 
But in any case you need return the same Payload exposed in the GraphQL schema.

For query operations like **list** operation it's similar but using the following convention:

Query Resolvers:
 
 `Query\{Node}\{OperationName}`
 
Then, if you need override the action `users` must create the following class:

````php
namespace App\Query\User;

class Users
{
    public function __invoke()
    {
       ...
    }
}
````

# Extends Resolvers

Override a resolver is helpful, but is a tedious task 
if you only need do something partially different to the default behavior. 
For example, prepare the object before save, or add a custom conditions to the query to fetch objects. 
In these scenarios is more helpful to extends from default resolvers.

GraphQL comes with a default resolver for each CRUD operation:

- Add: `Ynlo\GraphQLBundle\Mutation\AddNode`
- Update: `Ynlo\GraphQLBundle\Mutation\UpdateNode`
- Delete: `Ynlo\GraphQLBundle\Mutation\DeleteNode`
- List: `Ynlo\GraphQLBundle\Query\Node\AllNodesWithPagination`

Each resolver expose some helpful methods in order to customize some behaviors.

````php
namespace App\Query\User;

use Doctrine\ORM\QueryBuilder;
use Ynlo\GraphQLBundle\Query\Node\AllNodesWithPagination;

class Users extends AllNodesWithPagination
{
    public function configureQuery(QueryBuilder $qb)
    {
        $qb->andWhere('o.enabled = :enabled')
           ->setParameter('enabled', 'true');
    }
}
````
In the above example the `Users` resolver extends from the 
default list resolver and use the exposed method `configureQuery` to add a custom logic to the query.

Extending from default resolvers is helpful and is the recommended way for many scenarios, 
check all available exposed methods in each default resolver.

# Customizing Schema

Each CRUD operation has a predefined 
configuration but you can customize this configuration to meet your needs.

For example to add a description and use a different name to the default users list:

```php
/**
 * @ORM\Entity()
 * @ORM\Table()
 *
 * @GraphQL\ObjectType()
 * @GraphQL\QueryList(name="allUsers", description="Get list of users")
 */
class User implements NodeInterface
{
```
