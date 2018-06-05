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

use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;

/**
 * @Annotation()
 *
 * @Target("CLASS")
 */
final class InterfaceType
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
    public $exclusionPolicy = ObjectDefinitionInterface::EXCLUDE_ALL;

    /**
     * @var array
     */
    public $discriminatorMap = [];

    /**
     * @var string
     */
    public $discriminatorProperty;

    /**
     * When a interface extends from another interface in PHP but you need ignore this
     * second interface in the list of implemented interfaces in graphql.
     * For example when the interface is implemented partially
     * or the object is part of polymorphic object and other object in the graph
     * is using the interface instead of this.
     *
     * @var array
     */
    public $ignoreParent = [];

    /**
     * @var array
     */
    public $options = [];
}
