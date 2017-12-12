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
final class QueryGet
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
     * @var bool
     */
    public $pluralQuery = true;

    /**
     * @var string
     */
    public $pluralQueryName;

    /**
     * @var string
     */
    public $fetchBy = 'id';

    /**
     * @var string
     */
    public $deprecationReason;

    /**
     * @var bool
     */
    public $namespace = true;
}
