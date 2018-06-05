<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\AppBundle\Query\User\Field;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Demo\AppBundle\Entity\User;

/**
 * @GraphQL\Field(type="boolean")
 */
class isCurrent
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * isCurrent constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function __invoke(User $root)
    {
        if ($this->tokenStorage && $token = $this->tokenStorage->getToken()) {
            return $root === $token->getUser();
        }
    }
}
