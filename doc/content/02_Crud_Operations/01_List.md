The **list** operation is used to fetch multiple nodes from database.

To enable this operation must add the `QueryList` annotation to the entity.

````php
use Doctrine\ORM\Mapping as ORM;
use Ynlo\GraphQLBundle\Annotation as GraphQL;

/**
 * @ORM\Entity()
 * @ORM\Table()
 *
 * @GraphQL\ObjectType()
 * @GraphQL\QueryList()
 */
class User
{
....
````
Now you can view a new available query in the GraphiQL explorer to request a paginated list of users.

### Example Query:
````graphql
query {
  users(first: 5) {
    edges {
      node {
        username
      }
    }
  }
}
````
For more details and information about cursor based pagination, 
Edges & Nodes refer to the following [GraphQL Documentation](http://graphql.org/learn/pagination/#pagination-and-edges) 
or [Relay Specification](https://facebook.github.io/relay/graphql/connections.htm)