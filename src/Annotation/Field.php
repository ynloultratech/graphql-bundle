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
 */
final class Field
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $name;

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
     * @var int
     */
    public $maxConcurrentUsage = 0;

    /**
     * @var array
     */
    public $roles = [];

    /**
     * Use this field only for given types
     * helpful for polymorphic objects
     *
     * @var array
     */
    public $in = [];

    /**
     * Does not use this field for given types
     * helpful for polymorphic objects
     *
     * @var array
     */
    public $notIn = [];

    /**
     * @var array
     */
    public $options = [];
}
