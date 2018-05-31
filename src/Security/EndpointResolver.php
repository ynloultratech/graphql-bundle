<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;

class EndpointResolver
{
    /**
     * @var AuthorizationChecker
     */
    protected $authorizationChecker;

    /**
     * @var array
     */
    protected $endpointsConfig = [];

    public function __construct(AuthorizationChecker $authorizationChecker, array $endpointsConfig = [])
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->endpointsConfig = $endpointsConfig['endpoints'] ?? [];
    }

    public function resolveEndpoint(Request $request): ?string
    {
        if (empty($this->endpointsConfig)) {
            return DefinitionRegistry::DEFAULT_ENDPOINT;
        }

        foreach ($this->endpointsConfig as $endpoint => $config) {
            if (isset($config['host'])) {
                $host = $request->getHost();
                if (preg_match(sprintf('/%s/', $this->cleanExpression($config['host'])), $host)) {
                    $hostPassed = true;
                } else {
                    $hostPassed = false;
                }
            } else {
                $hostPassed = true;
            }

            if (isset($config['path'])) {
                $path = $request->getPathInfo();
                if (preg_match(sprintf('/%s/', $this->cleanExpression($config['path'])), $path)) {
                    $pathPassed = true;
                } else {
                    $pathPassed = false;
                }
            } else {
                $pathPassed = true;
            }

            if (isset($config['roles'])) {
                try {
                    $rolePassed = $this->authorizationChecker->isGranted($config['roles']);
                } catch (AuthenticationCredentialsNotFoundException $exception) {
                    $rolePassed = false;
                }
            } else {
                $rolePassed = true;
            }

            if ($rolePassed && $hostPassed && $pathPassed) {
                return $endpoint;
            }
        }

        return null;
    }

    private function cleanExpression($exp)
    {
        return preg_replace('/\//', '\/', $exp);
    }
}
