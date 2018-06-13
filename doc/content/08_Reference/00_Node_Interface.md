The official documentation say:

> The server must provide an interface called Node. That interface must include exactly one field, called id that returns a non‐null ID.
This id should be a globally unique identifier for this object, and given just this id, the server should be able to refetch the object.

To accomplish this, GraphQLBundle include this interface `Ynlo\GraphQLBundle\Model\NodeInterface` 
and should be implemented by every node.

# What's a Node

A node its like a Entity in doctrine, but not all entities are necessarily nodes.

See the following example:

````php
/**
 * @ORM\Entity()
 * @ORM\Table()
 *
 * @GraphQL\ObjectType()
 */
class User implements NodeInterface
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $username;

    /**
     * @var Profile
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Profile", inversedBy="user", cascade={"all"}, orphanRemoval=true)
     */
    protected $profile;
    
    ....
````
In the above entity example, **User** is a Node, the user can be fetched individually from database, removed etc.
For all this operations is required a **ID** to interact with desired user.

In the other hand the **Profile** is a 
[GraphQL Object](../03_Type_Definitions/01_Object_Types.md) but not necessarily a Node. 
Because the relation is **OneToOne** always you can access 
to the **Profile** using the **User** object `(user.profile)` and update using the same approach.

> The above is only a example, if it's necessary the `Profile` object can be a Node too.