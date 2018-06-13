All types in GraphQL are of two categories: input and output.

- **Output** types (or field types) are: [Scalar](02_Scalar_Types.md), 
[Enum](03_Enum_Types.md), [Object](01_Object_Types.md), [Interface](05_Interface_Type.md), Union
- **Input types** (or argument types) are: [Scalar](02_Scalar_Types.md), 
                                           [Enum](03_Enum_Types.md), InputObject

Obviously, NonNull and List types belong to both categories depending on their inner type.

Input objects are used for mutations or arguments for queries, 
in case of mutations the GraphQLBundle recommend the use of [Symfony Forms for Mutation Inputs](../04_Queries_&_Mutations/02_Mutations.md) 
, but if you need in some special case can use input objects too.

# Defining Input Objects

To define a input object must use the `@GraphQL\InputObjectType()`

````php
/**
 * @GraphQL\ObjectType()
 */
class OrderBy
{
````
Fields and others settings are very similar to common [Object Types](01_Object_Types.md)

> By default GraphQLBundle search for input objects in the Model folder and sub-folders of each bundle.

The following example demostrate the use case of input object in a query argument.

### Input Object Definition:
````php
namespace App\Model\Argument;

use Ynlo\GraphQLBundle\Annotation as GraphQL;

/**
 * @GraphQL\InputObjectType()
 */
class OrderBy
{
    /**
     * @GraphQL\Field(type="string!")
     */
    protected $field;

    /**
     * @GraphQL\Field(type="string")
     */
    protected $direction = 'ASC';
    ...
````

### GraphQL Query
````graphql
query {
  posts(first: 10, orderBy: {field: "date", direction: "ASC"}){
    ...
  }
}
````

> In the above example the field `direction` is a `string` 
but can be a [ENUM](03_Enum_Types.md) with 'ASC' and 'DESC' as values.