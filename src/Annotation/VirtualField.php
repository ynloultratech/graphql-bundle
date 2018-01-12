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

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation()
 *
 * @Target({"CLASS"})
 */
final class VirtualField
{
    /**
     * @var string
     *
     * @Required()
     */
    public $name;

    /**
     * @var string
     *
     * @Required()
     */
    public $expression;

    /**
     * @var string
     *
     * @Required()
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
     * @var string
     */
    public $complexity;

    /**
     * @var array
     */
    public $roles = [];

    /**
     * @var array
     */
    public $options = [];
}
