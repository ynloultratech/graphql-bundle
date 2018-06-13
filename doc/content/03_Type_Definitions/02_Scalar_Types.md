GraphQLBundle describes several built-in scalar types:

- **string**: String type
- **int**: Integer type
- **float**: Float type
- **bool**: Boolean type
- **id**: ID type
- **DateTime**: An ISO-8601 encoded UTC date string

# Writing Custom Scalar Types

In addition to built-in scalars, you can define your own scalar types. 
Typical examples of such types are **Email**, **Date**, **Url**, etc.

Scalar types use [naming convention](../08_Reference/02_Naming_Conventions.md) and should be created in:

`Type\{Name}Type`

> If the name of the class does not match with desired object name can use the property name or pass the `name`
 argument in the parent constructor.

````php
public function __construct(array $config = [])
{
   $this->name = 'SomeName';
   // or
   parent::__construct(['name'=>'SomeName']);
}
````

### Example:

````php
namespace App\Type;

use GraphQL\Type\Definition\ScalarType;

class DateTimeType extends ScalarType
{
    public function __construct(array $config = [])
    {
        $this->description = 'An ISO-8601 encoded UTC date string.';

        parent::__construct($config);
    }

    public function serialize($value)
    {
        if ($value instanceof \DateTime) {
            return $value->format('c');
        }

        return $value;
    }

    public function parseValue($value)
    {
        return \DateTime::createFromFormat('c', $value);
    }

    public function parseLiteral($valueNode)
    {
        return $this->parseValue($valueNode);
    }
}
````
> DateTime type is already a internal type of GraphQLBundle,
see [type system](00_Type_System.md).

For more information about create Scalar types read 
the official documentation of [graphql-php](http://webonyx.github.io/graphql-php/type-system/scalar-types/)