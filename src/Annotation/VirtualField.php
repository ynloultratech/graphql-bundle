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

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation()
 *
 * @Target({"CLASS", "METHOD"})
 */
final class VirtualField extends Annotation
{
    /**
     * @var string
     *
     * @required
     */
    public $type;

    /**
     * @var string
     */
    public $name;
}
