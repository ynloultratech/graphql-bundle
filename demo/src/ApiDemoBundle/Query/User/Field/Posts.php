<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Query\User\Field;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity\Post;
use Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity\User;

/**
 * @GraphQL\Field()
 * @GraphQL\Argument(name="first", type="int!")
 */
class Posts implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @return array
     */
    public function __invoke(User $root, int $first = null)
    {
        return $this->container
            ->get('doctrine')
            ->getRepository(Post::class)
            ->findBy(['author' => $root], null, $first);
    }
}
