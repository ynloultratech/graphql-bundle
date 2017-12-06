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
final class AllNodes extends Annotation
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
     * @var string
     */
    public $deprecationReason;
}
