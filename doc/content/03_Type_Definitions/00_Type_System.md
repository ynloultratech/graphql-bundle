GraphQL is all about types, the query language is
basically about selecting fields on objects.

[GraphQL Schemas and Types](http://graphql.org/learn/schema/#type-system)

# Writing Types

- [Object Type](01_Object_Types.md)
- [Interface Type](05_Interface_Type.md)
- [Scalar Type](02_Scalar_Types.md)
- [ENUM Type](03_Enum_Types.md)

# Using Types

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
>>> Type names should be unique in the entire schema

# List & NonNull

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
 
# graphql-php

Graphql Bundle works behind scenes with [graphql-php](https://github.com/webonyx/graphql-php) library, 
check this [documentation](https://webonyx.github.io/graphql-php/type-system/) to find a native and advanced way
to define object types.