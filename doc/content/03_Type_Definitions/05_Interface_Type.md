An Interface is an abstract type that includes a certain 
set of fields that a concrete type must include to implement the interface.

GraphQLBundle use PHP interfaces to accomplish 
this feature with the special annotation `@GraphQL\InterfaceType`

### Example:
````php
namespace App\Model;

use Ynlo\GraphQLBundle\Annotation as GraphQL;

/**
 * Use this interface in all entities that you need to automatically
 * set a timestamp on creation or update.
 *
 * @GraphQL\InterfaceType()
 */
interface TimestampableInterface
{
    /**
     * Get creation time.
     *
     * @return \DateTime
     *
     * @GraphQL\Field(type="datetime!")
     */
    public function getCreatedAt(): \DateTime;

    /**
     * Get the time of last update.
     *
     * @return \DateTime
     *
     * @GraphQL\Field(type="datetime!")
     */
    public function getUpdatedAt(): \DateTime;

    /**
     * Set creation time.
     *
     * @param \DateTime $createdAt
     *
     * @return TimestampableInterface
     */
    public function setCreatedAt(\DateTime $createdAt): TimestampableInterface;

    /**
     * Set the time of last update.
     *
     * @param \DateTime $updatedAt
     *
     * @return TimestampableInterface
     */
    public function setUpdatedAt(\DateTime $updatedAt): TimestampableInterface;
}
````
The above interface publish `createdAt` and `updatedAt` fields. 
The `Field` annotation must be used on every required method to expose in the interface.

>>> Setters method should not be exposed on interfaces, because interfaces are only used for **reading** purposes.