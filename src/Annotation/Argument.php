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
final class Argument
{
    public const UNDEFINED_ARGUMENT = 'UNDEFINED_ARGUMENT';

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
     * @var mixed
     */
    public $defaultValue = self::UNDEFINED_ARGUMENT;

    /**
     * Use when public argument name does not match with method name
     * e.g. userId (public) => $id (method), commonly used for reusable resolvers
     *
     * @var string
     */
    public $internalName;

    /**
     * @var array
     */
    public $options = [];
}
