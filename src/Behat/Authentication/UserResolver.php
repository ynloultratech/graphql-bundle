<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Authentication;

use Symfony\Component\HttpKernel\Kernel;

class UserResolver implements UserResolverInterface
{
    /**
     * @var Kernel
     */
    protected $kernel;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    public function findByUsername($username)
    {
        $container = $this->kernel->getContainer();
        $userClass = $container->getParameter('graphql.security.user.class');

        return $container->get('doctrine')->getManager()->getRepository($userClass)->findOneBy(['username' => $username]);
    }
}
