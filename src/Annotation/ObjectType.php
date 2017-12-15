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
     * @var array
     */
    public $options = [];
}
