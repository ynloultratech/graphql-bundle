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
final class Filter
{
    /**
     * Filter input object type
     *
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $name;

    /**
     * Filter class implementing FilterInterface
     *
     * @var string
     */
    public $resolver;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $deprecationReason;

    /**
     * Name of the field directly related to this filter.
     * The filter is linked to this field and only is
     * available if this field is available.
     *
     * @var string
     */
    public $field;

    /**
     * @var array
     */
    public $options = [];
}
