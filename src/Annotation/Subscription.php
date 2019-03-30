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
class Subscription
{
    /**
     * @var string
     */
    public $name;

    /**
     * @Required()
     */
    public $payload;

    /**
     * Name of the field to display the subscription payload
     * by default 'data'
     *
     * @var string
     */
    public $fieldName;

    /**
     * @var string
     */
    public $fieldDescription;

    /**
     * @var string
     */
    public $node;

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
    public $resolver;

    /**
     * @var array
     */
    public $options = [];
}
