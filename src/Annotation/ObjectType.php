<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Enum;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;

/**
 * @Annotation()
 *
 * @Target("CLASS")
 */
final class ObjectType
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $description;

    /**
     * @Enum({"ALL", "NONE"})
     *
     * @var string
     */
    public $exclusionPolicy = ObjectDefinitionInterface::EXCLUDE_NONE;

    /**
     * When the object implements a interface in PHP but you need ignore this
     * interface in the list of implemented interfaces in graphql.
     * For example when the interface is implemented partially
     * or the object is part of polymorphic object and other object in the graph
     * is using the interface instead of this.
     *
     * @var array
     */
    public $ignoreInterface = [];

    /**
     * @var array
     */
    public $options = [];
}
