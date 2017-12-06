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
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation()
 * @Target("CLASS")
 */
final class Mutation extends Annotation
{
    /**
     * @var string
     * @Required()
     */
    public $name;

    /**
     * The node type you are attempting to modify
     *
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $description;

    /**
     * @var array
     */
    public $validationGroups = [];

    /**
     * Create one only argument called input with the given type and fields
     * If any argument is specified and $argsToInput is true,
     * all arguments will be prepend to the input type given
     *
     * @var string
     */
    public $input;

    /**
     * Mutation arguments
     *
     * @var array
     */
    public $args = [];

    /**
     * Create one only argument called
     * input with fields using given arguments
     *
     * @var bool
     */
    public $argsToInput = false;

    /**
     * Automatically add a field(input) or argument called `clientMutationId`
     *
     * @see https://facebook.github.io/relay/graphql/mutations.htm#sec-Mutation-inputs
     *
     * @var bool
     */
    public $addMutationId = true;

    /**
     * The return type expected by this mutation,
     * can be a string to define a custom type or a array of fields
     * to automatically create a mutation payload
     *
     * @var mixed
     * @required
     */
    public $returns;

    /**
     * @var string
     */
    public $resolver;

    /**
     * @var string
     */
    public $deprecationReason;
}