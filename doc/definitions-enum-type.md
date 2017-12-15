# ENUM Type

Enumeration types are a special kind of scalar that is restricted to a particular set of allowed values.

Enum types should be created like [scalar types](definitions-scalar-type.mds) in:

`Type\{Name}Type`

######Example:
````php
namespace AppBundle\Type;

use GraphQL\Type\Definition\EnumType;

class PostStatusType extends EnumType
{
    public const DRAFT = 'DRAFT';
    public const PENDING = 'PENDING';
    public const PUBLISH = 'PUBLISH';

    public function __construct()
    {
        $config = [
            'name' => 'PostStatus',
            'values' => [
                self::DRAFT => [
                    'description' => 'The post is in draft',
                ],
                self::PENDING => [
                    'description' => 'The post is ready to publish pending review',
                ],
                self::PUBLISH => [
                    'description' => 'The post has been published',
                ],
            ],
        ];

        parent::__construct($config);
    }
}
````

For more information about create ENUM types read 
the official documentation of [graphql-php](http://webonyx.github.io/graphql-php/type-system/enum-types/)