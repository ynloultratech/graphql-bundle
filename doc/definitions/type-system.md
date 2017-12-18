# Type System

GraphQL is all about types, the query language is
basically about selecting fields on objects.

[GraphQL Schemas and Types](http://graphql.org/learn/schema/#type-system)

## Writing Types

- [Object Type](object-type.md)
- [Interface Type](interface-type.md)
- [Scalar Type](scalar-type.md)
- [ENUM Type](enum-type.md)

## Using Types

GraphQLBundle support the use of FQN of classes to set types.

````php
/**
 * @GraphQL\Field(type="App\Entity\User")
 */
 protected $author;
````
is similar to:
````php
/**
 * @GraphQL\Field(type="User")
 */
 protected $author;
````
> Type names should be unique in the entire schema

#### List & NonNull

You can apply additional type modifiers that affect validation of those values. 

Let's look at an example:

````php
/**
 * @GraphQL\Field(type="[App\Entity\Category]")
 */
 protected $categories;
````
We can use a type modifier to mark a type as a List, 
which indicates that this field will return an array of that type. 

In the schema language, this is denoted by wrapping the type in square brackets, [ and ].

A type can be marked as Non-Null by adding an exclamation mark, ! after the type name

 ````php
 /**
  * @GraphQL\Field(type="App\Entity\User!")
  */
  protected $author;
 ````
 
 Read more information about [List & NonNull](http://graphql.org/learn/schema/#lists-and-non-null)