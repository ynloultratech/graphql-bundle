Object Type is the most frequently 
used primitive in a typical GraphQL application.

Conceptually Object Type is a collection of [Fields](06_Fields.md). 
Each field, in turn, has its own type which allows building complex hierarchies.

# Defining Objects

To define a GraphQL object must use the `@GraphQL\ObjectType()`

````php
/**
 * @GraphQL\ObjectType()
 */
class User
{
````
>> To define a object as **Node** the object must implements `Ynlo\GraphQLBundle\Model\NodeInterface`.
 See [this documentation](../08_Reference/01_Object_ID.md) to know more about nodes.
 
Options:
- **name**: Name to expose the object, if not set will be automatically resolved.
- **description**: Object description to expose in the documentation
- **exclusionPolicy**: Hide or show all fields by default
- **options**: Options are used by [plugins](../07_Advanced/99_Definitions_Plugins.md) to provide extra features

By default when a object is annotated with `@GraphQL\ObjectType` annotation all properties are exposed. 
If you need exclude some properties can use the `@GraphQL\Exclude()` annotation.

````php
/**
 * @var string
 *
 * @ORM\Column(type="string")
 *
 * @GraphQL\Exclude()
 */
protected $password;
````

To change the default behavior and exclude all properties by default must change the `exclusionPolicy`.

````php
/**
 * @GraphQL\ObjectType(exclusionPolicy="ALL")
 */
class User
````
Now is required the use of `@GraphQL\Expose()` in desired properties.

# Fields

To define object fields must use `@GraphQL\Field` annotation. 

````php
/**
 * @GraphQL\Field(type="string")
 */
protected $username;
````

> If the object is a Doctrine Entity mostly all properties are automatically resolved.
Read more about [fields definitions](06_Fields.md)