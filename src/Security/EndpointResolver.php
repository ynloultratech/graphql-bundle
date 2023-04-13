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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Extension\EndpointNotValidException;

class EndpointResolver
{
    /**
     * @var DefinitionRegistry
     */
    protected $definitionRegistry;

    /**
     * @var AuthorizationChecker
     */
    protected $authorizationChecker;

    /**
     * @var array
     */
    protected $endpointsConfig = [];

    /**
     * EndpointResolver constructor.
     *
     * Endpoints config should have the following format
     *
     * [
     * 'endpoints' => [
     *   'name' => [
     *      'roles'=> [],
     *      'host' => '',
     *      'path' => ''
     *    ]
     *  ]
     * ]
     *
     * @param DefinitionRegistry            $definitionRegistry
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param array                         $endpointsConfig
     */
    public function __construct(DefinitionRegistry $definitionRegistry, AuthorizationCheckerInterface $authorizationChecker, array $endpointsConfig = [])
    {
        $this->definitionRegistry = $definitionRegistry;
        $this->authorizationChecker = $authorizationChecker;
        $this->endpointsConfig = $endpointsConfig['endpoints'] ?? [];
    }

    /**
     * @param Request $request
     *
     * @return null|Endpoint
     *
     * @throws EndpointNotValidException
     */
    public function resolveEndpoint(Request $request): ?Endpoint
    {
        if (empty($this->endpointsConfig)) {
            return $this->definitionRegistry->getEndpoint();
        }

        foreach ($this->endpointsConfig as $endpoint => $config) {
            if (isset($config['host'])) {
                $host = $request->getHost();
                if (preg_match(sprintf('/%s/', (string) $this->cleanExpression($config['host'])), $host)) {
                    $hostPassed = true;
                } else {
                    $hostPassed = false;
                }
            } else {
                $hostPassed = true;
            }

            if (isset($config['path'])) {
                $path = $request->getPathInfo();
                if (preg_match(sprintf('/%s/', (string) $this->cleanExpression($config['path'])), $path)) {
                    $pathPassed = true;
                } else {
                    $pathPassed = false;
                }
            } else {
                $pathPassed = true;
            }

            if (isset($config['roles'])) {
                try {
                    $rolePassed = true;
                    foreach ($config['roles'] as $role) {
                        if (!$this->authorizationChecker->isGranted($role)) {
                            $rolePassed = false;
                            break;
                        }
                    }
                } catch (AuthenticationCredentialsNotFoundException $exception) {
                    $rolePassed = false;
                }
            } else {
                $rolePassed = true;
            }

            if ($rolePassed && $hostPassed && $pathPassed) {
                return $this->definitionRegistry->getEndpoint($endpoint);
            }
        }

        return null;
    }

    /**
     * @param string $exp
     *
     * @return null|string|string[]
     */
    private function cleanExpression($exp)
    {
        return preg_replace('/\//', '\/', $exp);
    }
}
