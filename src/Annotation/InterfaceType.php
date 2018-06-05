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
    public $exclusionPolicy = ObjectDefinitionInterface::EXCLUDE_NONE;

    /**
     * @var array
     */
    public $discriminatorMap = [];

    /**
     * @var string
     */
    public $discriminatorProperty;

    /**
     * @var array
     */
    public $options = [];
}
