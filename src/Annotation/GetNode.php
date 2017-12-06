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

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation()
 *
 * @Target({"CLASS"})
 */
final class GetNode extends Annotation
{
    /**
     * @var string
     */
    public $node;

    /**
     * @var string
     */
    public $queryName;

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
}
