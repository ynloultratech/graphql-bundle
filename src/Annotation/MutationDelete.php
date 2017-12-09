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
use Ynlo\GraphQLBundle\Form\Node\NodeDeleteInput;

/**
 * @Annotation()
 *
 * @Target({"CLASS"})
 */
final class MutationDelete extends Mutation
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $resolver;

    /**
     * @var string
     */
    public $form = NodeDeleteInput::class;

    /**
     * @var string
     */
    public $payload;
}
