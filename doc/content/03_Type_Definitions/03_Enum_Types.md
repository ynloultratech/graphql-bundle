Enumeration types are a special kind of scalar that is restricted to a particular set of allowed values.

Enum types should be created like [scalar types](02_Scalar_Types.md) in:

`Type\{Name}Type`

### Example:
````php
namespace App\Type;

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

# DoctrineEnumBundle

GraphQLBundle has build-in support for [DoctrineEnumBundle](https://github.com/fre5h/DoctrineEnumBundle) 
and automatically register all enum types registered by this bundle.

> By default all enum types from DoctrineEnumBundle are registered 
using only their values without any extra information.

In order to allow add some extra information for this type of ENUM 
must extend from `Ynlo\GraphQLBundle\Doctrine\DBAL\Types\AbstractEnumType` 
instead of the DoctrineENUMBundle type.

### Example:
````php
namespace App\DBAL\Types;

use Ynlo\GraphQLBundle\Doctrine\DBAL\Types\AbstractEnumType;

class PostStatusType extends AbstractEnumType
{
    public const PUBLISH = 'PUBLISH';
    public const DRAFT = 'DRAFT';
    public const PENDING = 'PENDING';
    public const TRASH = 'TRASH';

    /** @deprecated */
    public const DELETED = 'DELETED';
    
    protected static $choices = [
        self::PUBLISH => 'Publish',
        self::DRAFT => 'Draft',
        self::PENDING => 'Pending',
        self::TRASH => 'Trash',
        self::DELETED => 'Deleted',
    ];

    protected static $descriptions = [
        self::PUBLISH => 'Viewable by everyone.',
        self::DRAFT => 'Incomplete post viewable by anyone with proper user role.',
        self::PENDING => 'Awaiting a user with permissions tu publish.',
        self::TRASH => 'Posts in the Trash are assigned the trash status.',
        self::DELETED => 'The post has been marked as deleted',
    ];

    protected static $deprecatedReasons = [
        self::DELETED => 'This status is useless, 
        deleted post has been removed from database permanently. 
        For temporal removal use TRASH instead.',
    ];

    protected static $publicNames = [
        self::PUBLISH => 'PUBLISHED',
    ];
}
````

-  **descriptions:** Descriptions to display in the documentation
-  **deprecatedReasons:** Every deprecated field with the deprecated reason
-  **publicNames:** Publish internal values with other names, 
in the above example **PUBLISH** is the internal value, but **PUBLISHED** is de public one for API consumers.

>>> GraphQL Bundle override the symfony form type for Doctrine ENUM Types. 
For that reason a graphical interface using 
symfony forms and DoctrineENUMBundle may does not work as expected. 
A best practice to avoid this type of issues is build API services separate of any other application logic,
 either through a multi kernel or differents small apps.