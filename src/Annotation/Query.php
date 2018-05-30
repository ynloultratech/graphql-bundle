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

/**
 * @Annotation()
 *
 * @Target({"CLASS", "ANNOTATION"})
 */
final class Query
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $deprecationReason;

    /**
     * @var \Ynlo\GraphQLBundle\Annotation\Argument[]
     */
    public $arguments = [];

    /**
     * @var string
     */
    public $resolver;

    /**
     * @var array
     */
    public $roles = [];

    /**
     * Options used by plugins
     *
     * @var array
     */
    public $options = [];
}
