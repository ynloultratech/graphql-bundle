<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Annotation\Plugin;

use Doctrine\Common\Annotations\Annotation\Enum;

/**
 * @Annotation
 */
class Pagination extends PluginConfigAnnotation
{
    /**
     * @var bool
     */
    public $enabled;

    /**
     * Max number of records allowed for first & last
     *
     * @var int
     */
    public $limit;

    /**
     * @var string[]
     */
    public $orderBy = ['*'];

    /**
     * @var array
     */
    public $filters = ['*'];

    /**
     * Target node to properly paginate.
     * If is possible will be auto-resolved using naming conventions
     *
     * @var int
     */
    public $target;

    /**
     * When is used in sub-fields should be the field to filter by parent instance
     *
     * @var string
     */
    public $parentField;

    /**
     * When is used in sub-fields should be the type of relation with the parent field
     *
     * @Enum({"ONE_TO_MANY", "MANY_TO_MANY"})
     *
     * @var string
     */
    public $parentRelation;
}
