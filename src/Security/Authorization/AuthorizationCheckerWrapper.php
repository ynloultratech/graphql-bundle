<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Security\Authorization;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

class AuthorizationCheckerWrapper implements AuthorizationCheckerInterface
{
    private static $cache = [];

    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted($attributes, $subject = null): bool
    {
        if (null !== $subject) {
            $key = md5(json_encode($attributes).'/'.spl_object_hash($subject));
        } else {
            $key = md5(json_encode($attributes));
        }

        if (array_key_exists($key, static::$cache)) {
            return static::$cache[$key];
        }

        try {
            $isGranted = $this->authorizationChecker->isGranted($attributes, $subject);
        } catch (AuthenticationCredentialsNotFoundException $e) {
            $isGranted = true;
        }

        return static::$cache[$key] = $isGranted;
    }
}
