CRUD operations are powerful, 
and the integration of extensions ensure a high level of customization.

# Extensions for Interfaces

Imagine you have two entities, a Post and a Comment:

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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="posts")
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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="posts")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $author;
    
    // ...
````    
As you can see, each entity shares the field `author`, 
this field should be populated with the current user 
every time a `Post` or a `Comment` is created.

For this scenario [override the resolver](05_Customizing.md)
to use `prePersist` is not a good and reusable solution.

First of all you need detect this type of scenarios and take proper actions. For example
in this case must create a new interface.

````php
namespace App\Model;

use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Demo\App\Entity\User;

/**
 * @GraphQL\InterfaceType()
 */
interface HasAuthorInterface
{
    /**
     * @GraphQL\Field(type="App\Entity\User")
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
> **NOTE:** The annotation `InterfaceType` is not required but should be 
used if you want publish the interface in your graphql schema.
In case you need the same behavior without publishing any in the schema
ensure a correct naming convention when interface name "HasAuthorInterface" 
must use extension name "HasAuthorExtension". The system automatically load this extension
for all object implementing this interface.

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

Extensions use [naming convention](../08_Reference/02_Naming_Conventions.md) and should be created in:

`Extension\{InterfaceName}Extension`

> GraphQLBundle use naming convention to detect extensions for each interface. 

Then, to create the extension for the above example the class should be:

````php
namespace App\Extension;

use Ynlo\GraphQLBundle\Extension\AbstractExtension;

class HasAuthorExtension extends AbstractExtension
{
    //...
}
````

> Extensions must implements `Ynlo\GraphQLBundle\Extension\ExtensionInterface` 
or extends from `Ynlo\GraphQLBundle\Extension\AbstractExtension`

If you need the container in your extension you can implements 
`Symfony\Component\DependencyInjection\ContainerAwareInterface` and the container
is automatically injected.

> As of symfony4 is not recommended use the container directly to get access to other services. 
Instead is recommended inject all dependencies in the service constructor. 
To register the extension as a service must create the extension using the tag `graphql.extension`

Autowiring example for symfony 3.4 or 4+

````yaml
services:
    App\Extension\:
        resource: '../src/Extension/*'
        tags: ['graphql.extension']
````

At this point your extension is ready but it does not do anything yet. 
In this case whe need set the current user as author of every created `Pos` or `Comment`.

````php
namespace App\Extension;

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

# Prioritizing extensions

By default all extensions are executed in a arbitrary order, this is functional in many cases. 
But if you need execute some extension with a highest or lowest priority can use services priorities in the tag.