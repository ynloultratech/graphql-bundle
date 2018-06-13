# Queries

GraphQLBundle comes with some build-in [CRUD operations](../02_Crud_Operations/00_Overview.md) 
but almost always is necessary add others custom queries.

## Defining Queries

To define a Query must use the `@GraphQL\Query()` annotation
 and create the [resolver](../08_Reference/03_Resolvers.md) using [naming convention](../08_Reference/02_Naming_Conventions.md)

Query Resolvers:
 
 `Query\{Node}\{OperationName}`
 
Then, if you need create a custom query for `User` node:

````php
namespace App\Query\User;

/**
 * @GraphQL\Query(type="[]")
 */
class AdminUsers
{
    public function __invoke()
    {
       ...
    }
}
````
> The above query does not define a specific type for 
the query because is automatically guessed using naming convention.
In this case `[]` has the same result as `[User]` or `[App\Entity\User]`.

Options:
- **name**: Name to expose the query, if not set will be automatically resolved.
- **description**: Query description to expose in the documentation
- **type**: The return type of the query, can use NonNull and List modifiers
- **arguments**: Query arguments, *only needed when override CRUD operations*
- **resolver**: Query resolver, *only needed when override CRUD operations*
- **deprecationReason**: Mark the field as deprecated with the following reason
- **options**: Options are used by [plugins](../07_Advanced/99_Definitions_Plugins.md) to provide extra features
 
## Query Response

The response of each query should match with the exposed type in the GraphQL schema. 
If your query expose a `User` type as query result then must return this type of object in the return statement in the resolver.

````
namespace App\Query\User;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Ynlo\GraphQLBundle\Annotation as GraphQL;
use App\Entity\User;

/**
 * @GraphQL\Query()
 * @GraphQL\Argument(name="username", type="String!")
 */
class GetUserByUsername implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function __invoke($username)
    {
        return $this->container
            ->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy(['username' => $username]);
    }
}
````
