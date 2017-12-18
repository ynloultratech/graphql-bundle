# Input Object Type

All types in GraphQL are of two categories: input and output.

- **Output** types (or field types) are: [Scalar](scalar-type.md), 
[Enum](enum-type.md), [Object](object-type.md), [Interface](interface-type.md), Union
- **Input types** (or argument types) are: [Scalar](scalar-type.md), 
                                           [Enum](enum-type.md), InputObject

Obviously, NonNull and List types belong to both categories depending on their inner type.

Input objects are used for mutations or arguments for queries, 
in case of mutations the GraphQLBundle recommend the use of [Symfony Forms for Mutation Inputs](../mutations/input-forms.md) 
, but if you need in some special case can use input objects too.

## Defining Input Objects

To define a input object must use the `@GraphQL\InputObjectType()`

````php
/**
 * @GraphQL\ObjectType()
 */
class OrderBy
{
````
Fields and others settings are very similar to common [Object Types](object-type.md)

> By default GraphQLBundle search for input objects in the Model folder and sub-folders of each bundle.

The following example demostrate the use case of input object in a query argument.

###### Input Object Definition:
````php
namespace AppBundle\Model\Argument;

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

##### GraphQL Query
````graphql
query {
  posts(first: 10, orderBy: {field: "date", direction: "ASC"}){
    ...
  }
}
````

> In the above example the field `direction` is a `string` 
but can be a [ENUM](enum-type.md) with 'ASC' and 'DESC' as values.