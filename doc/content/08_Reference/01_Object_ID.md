One requirement for Relay’s object management is implementing the "Node" interface.
The NodeInterface assume globally unique IDs for data fetching. 

# Global ID

A system without globally unique IDs can usually synthesize them 
by combining the type with the type-specific ID, which is what was done in this bundle.

Example:

`User:1` <=> `VXNlcjox`

- NodeType : **User**
- DatabaseID : **1**

The IDs we got back were base64 strings. 
IDs are designed to be opaque and base64 string is a useful convention in GraphQL 
to remind viewers that the string is an opaque identifier.

> Complete details on how the server should behave are available in 
the [GraphQL Object Identification](https://facebook.github.io/relay/docs/en/graphql-object-identification.html) spec.

# ID Encoders

ID encoders are used to convert the object to global identifier, 
this bundle comes with three build-in encoders:

- `SimpleIDEncoder` = `Post:1` Output a simple object name and database ID without apply any base64 codification. 
This encoder is helpful for debugging purposes.
- `Base64IDEncoder` = `UG9zdDox` Like simple encoder but with base64 codification applied, **CAN** be easily decoded by API consumers.
- `SecureIDEncoder` = `akYlIAXk` Like simple encoder, but with encryption applied, **CAN NOT** be easily decoded by API consumers.

> Since v1.1 of GraphQL Bundle the default encoder is `SecureIDEncoder`.

You can create you custom encoder implementing `\Ynlo\GraphQLBundle\Encoder\IDEncoderInterface`

Example:

````php
<?php
namespace App\Encoder;

use Ynlo\GraphQLBundle\Encoder\SimpleIDEncoder;
use Ynlo\GraphQLBundle\Model\NodeInterface;

class MyIDEncoder extends SimpleIDEncoder
{
    /**
     * {@inheritDoc}
     */
    public function encode(NodeInterface $node): ?string
    {
        return myEncodeMethod(parent::encode($node));
    }

    /**
     * {@inheritDoc}
     */
    public function decode($globalId): ?NodeInterface
    {
        return parent::decode(myDecodeMethod($globalId));
    }
}
````

The above encoder use custom methods `myEncodeMethod` and `myDecodeMethod` 
to encode and decode global Ids returned by simple encoder.

Now register you class as service and configure your encoder in graphql settings:

````
graphql:
    id_encoder: App\Encoder\MyIDEncoder
````

> NOTE! Is recommended in production environments use secure global ids in order to avoid consumers to decode your ids easily. 
Otherwise is easy fetch any arbitrary record guessing the type and ID using the `node` query. Anyway is recommended
use [access control](../05_Security/05_Access_Control.md) to protect your data for unauthorized users.

# NodeInterface

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
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Profile", inversedBy="user", cascade={"all"}, orphanRemoval=true)
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