The polymorphism in GraphQLBundle is the possibility to use the same class as different graphql object types.

Imagine you have a `User` entity, but you has admin users and customers users, saved using the same entity.
Commonly customers have different properties than admin users. Example, customer user can have a field called `orders`
to get the list of all Orders and/or field called `payments` to get payments.

To convert a node to polymorphic object use `InterfaceType` to the main object instead of `ObjectType`.

````php
<?php
namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Model\NodeInterface;

/**
 * @ORM\Entity()
 * @ORM\Table()
 *
 * @GraphQL\InterfaceType(
 *  discriminatorProperty="type",
 *  discriminatorMap={"CUSTOMER":"Customer", "ADMIN":"Admin"}
 * )
 * @GraphQL\ObjectType(name="Customer")
 * @GraphQL\ObjectType(name="Admin")
 * @GraphQL\QueryList()
 */
class User implements NodeInterface
{
 /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @GraphQL\Field(name="login", type="string")
     */
    protected $username;
    
    /**
     * @var string
     *
     * @GraphQL\Exclude()
     */
    protected $type;

     /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Order", mappedBy="customer", fetch="EXTRA_LAZY")
     *
     * @GraphQL\Field(in={"Customer"})
     */
    protected $orders;
}
````
The above example create a interface called `User` and two objects implementing this interface `Customer` and `Admin`.
A query to fetch `allUsers` is created too.

- `discriminatorProperty`: The object property to get the object type. Can be exposed in the API or not.
- `discriminatorMap`: Specifies which values of the discriminator property identify a object as being of which type.

The following example fetch all users and display latest orders for customers users.

<div class="graphiql">
<div class="request">

````graphql
query users {
  users {
    all(first: 5) {
      edges {
        node {
          __typename
          id
          ... on Customer {
            orders(last: 10) {
              edges {
                node {
                  amount
                }
              }
            }
          }
        }
      }
    }
  }
}
````

</div>
<div class="response">

````
{
  "data": {
    "users": {
      "all": {
        "edges": [
          {
            "node": {
              "__typename": "Admin",
              "id": "QWRt2VyOjE="
            }
          },
          {
            "node": {
              "__typename": "Customer",
              "id": "Q29tbVXNlcjoy",
              "orders": {
                "edges": [
                  {
                    "node": {
                      "amount": 100
                    }
                  },
                  {
                    "node": {
                      "amount": 23
                    }
                  }
                ]
              }
            }
          },
          {
            "node": {
              "__typename": "Customer",
              "id": "Q29tbVXNlcjoz",
              "orders": {
                "edges": [
                  {
                    "node": {
                      "amount": 50
                    }
                  }
                ]
              }
            }
          },
          {
            "node": {
              "__typename": "Customer",
              "id": "Q29tbVXNlcjo0",
              "orders": {
                "edges": [
                  {
                    "node": {
                      "amount": 40
                    }
                  },
                  {
                    "node": {
                      "amount": 55
                    }
                  }
                ]
              }
            }
          },
          {
            "node": {
              "__typename": "Customer",
              "id": "Q29tbVXNlcjo1",
              "orders": {
                "edges": [
                  {
                    "node": {
                      "amount": 20
                    }
                  }
                ]
              }
            }
          }
        ]
      }
    }
  }
}
````

</div>
</div>

## Properties for specific objects

For each property in the class can define `in` and `notIn` in the field annotation to use
this property only for certain type of objects. Must use concrete object types names in this place
and not the interface name.

````php
 /**
 * @var Collection
 *
 * @ORM\OneToMany(targetEntity="App\Entity\Order", mappedBy="customer", fetch="EXTRA_LAZY")
 *
 * @GraphQL\Field(in={"Customer"})
 */
protected $orders;
````
The above example only display the field `orders` for `Customer` objects.