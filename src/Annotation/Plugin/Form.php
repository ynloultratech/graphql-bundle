<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Annotation\Plugin;

/**
 * @Annotation
 */
class Form extends PluginConfigAnnotation
{
    /**
     * @var bool
     */
    public $enabled;

    /**
     * Specify the form type to use,
     * [string] Name of the form type to use
     * [true|null] The form will be automatically resolved to ...Bundle\Form\Input\{Node}\{MutationName}Input.
     * [true] Throw a exception if the form can`t be located
     * [false] The form is not required and should not be resolved
     *
     * @var mixed
     */
    public $type;

    /**
     * Form options
     *
     * @var array
     */
    public $options = [];

    /**
     * Name of the argument to use as input
     *
     * @var string
     */
    public $argument;

    /**
     * Automatically add a field called clientMutationId
     *
     * @var bool
     */
    public $clientMutationId;
}
