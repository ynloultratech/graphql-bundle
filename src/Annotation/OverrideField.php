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
 * @Target({"CLASS"})
 */
final class OverrideField
{
    /**
     * @var string
     *
     * @required
     */
    public $name;

    /**
     * Override a parent field with other name
     *
     * @var string
     */
    public $alias;

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
     * @var string
     */
    public $complexity;

    /**
     * Ignore this field
     *
     * @var bool
     */
    public $hidden;

    /**
     * @var array
     */
    public $in = [];

    /**
     * @var array
     */
    public $notIn = [];

    /**
     * @var array
     */
    public $options = [];
}
