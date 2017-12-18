# CRUD Extensions

CRUD operations are powerful, 
and the integration of extensions ensure a high level of customization.

## Extensions for Interfaces

Extensions are designed for [interfaces](../definitions/interface-type.md) 
to add extra features for common operations.

For example, imagine you have two entities, a Post and a Comment:

````php
/**
 * @ORM\Entity()
 * @ORM\Table()
 *
 * @GraphQL\ObjectType()
 */
class Post implements NodeInterface
{
    // ...
    
    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="posts")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $author;
    
    // ...
````    

````php
/**
 * @ORM\Entity()
 * @ORM\Table()
 *
 * @GraphQL\ObjectType()
 */
class Comment implements NodeInterface
{
    // ...
    
    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="posts")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $author;
    
    // ...
````    
As you can see, each entity shares the field `author`, 
this field should be populated with the current user 
every time a `Post` or a `Comment` is created.

For this scenario [override the resolver](customizing.md)
to use `prePersist` is not a good and reusable solution.

First of all you need detect this type of scenarios and take proper actions. For example
in this case should create a new [interface](../definitions/interface-type.md) `HasAuthorInterface`

````php
namespace AppBundle\Model;

use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Demo\AppBundle\Entity\User;

/**
 * @GraphQL\InterfaceType()
 */
interface HasAuthorInterface
{
    /**
     * @GraphQL\Field(type="AppBundle\Entity\User")
     *
     * @return null|User
     */
    public function getAuthor():?User;

    /**
     * @param User $user
     *
     * @return HasAuthorInterface
     */
    public function setAuthor(User $user): HasAuthorInterface;
}
````
And use the interface in your objects.

````php
class Post implements NodeInterface, HasAuthorInterface
````
````php
class Comment implements NodeInterface, HasAuthorInterface
````

> By defining an interface and then implementing it, 
you can guarantee a "contract" for consumers of a class.
Interfaces can be used across unrelated classes to share behaviors.

Now you are able to create a extension to manage 
the behavior for any concrete object implementing this interface.

Extensions use [naming convention](../naming-conventions.md) and should be created in:

`Extension\{InterfaceName}Extension`

> GraphQLBundle use naming convention to detect extensions for each interface. 

Then, to create the extension for the above example the class should be:

````php
namespace AppBundle\Extension;

use Ynlo\GraphQLBundle\Extension\AbstractExtension;

class HasAuthorExtension extends AbstractExtension
{
    //...
}
````

> Interface extensions must implements `Ynlo\GraphQLBundle\Extension\ExtensionInterface` 
or extends from `Ynlo\GraphQLBundle\Extension\AbstractExtension`

At this point your extension is ready but it does not do anything yet. 
In this case whe need set the current user as author of every created `Pos` or `Comment`.

````php
namespace AppBundle\Extension;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Form\FormEvent;
use Ynlo\GraphQLBundle\Extension\AbstractExtension;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Resolver\ResolverContext;

class HasAuthorExtension extends AbstractExtension implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function onSubmit(FormEvent $event)
    {
        //set the logic to detect the user
        //...
        
        $event->getData()->setAuthor($user);
    }
}
````
Now your extension is functional and every time a `Pos` or `Comment` are created the author will be set.
Besides that, any other entity created implementing `HasAuthorInterface` reproduce this behavior.

> `onSubmit` it is one of many capabilities, review all methods inside `Ynlo\GraphQLBundle\Extension\ExtensionInterface` 
to see all of them.

## Prioritizing extensions

By default all extensions are executed in a arbitrary order, this is functional in many cases. 
But if you need execute some extension with a highest or lowest priority can use the `getPriorityMethod`

````php
public function getPriority(): int
{
    return 100;
}
````
The range of priority should be a number between -250...250, where highest numbers will be executed firstly. 