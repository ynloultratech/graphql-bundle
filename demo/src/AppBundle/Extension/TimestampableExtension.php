<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\AppBundle\Extension;

use Ynlo\GraphQLBundle\Demo\AppBundle\Model\TimestampableInterface;
use Ynlo\GraphQLBundle\Extension\AbstractExtension;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Resolver\ResolverContext;

/**
 * TimestampableExtension
 */
class TimestampableExtension extends AbstractExtension
{
    /**
     * {@inheritDoc}
     */
    public function prePersist(NodeInterface $node, $resolver, ResolverContext $context)
    {
        if ($node instanceof TimestampableInterface) {
            $node->setCreatedAt(new \DateTime());
            $node->setUpdatedAt(new \DateTime());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function preUpdate(NodeInterface $node, $resolver, ResolverContext $context)
    {
        if ($node instanceof TimestampableInterface) {
            $node->setUpdatedAt(new \DateTime());
        }
    }
}
