<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Authentication\JWT;

use Symfony\Component\HttpKernel\Kernel;

class LexikJWTGenerator implements TokenGeneratorInterface
{
    /**
     * @var Kernel
     */
    protected $kernel;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    public function generate($user)
    {
        $container = $this->kernel->getContainer();

        return $container->get('lexik_jwt_authentication.jwt_manager')->create($user);
    }
}
