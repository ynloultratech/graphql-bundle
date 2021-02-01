<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Security;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Security\EndpointResolver;

class EndpointResolverTest extends MockeryTestCase
{

    /**
     * @dataProvider getData
     */
    public function testResolveEndpoint($expected, $config, Request $request)
    {
        $registry = \Mockery::mock(DefinitionRegistry::class);
        $registry->allows('getEndpoint')->withAnyArgs()->andReturnUsing(
            function ($name = DefinitionRegistry::DEFAULT_ENDPOINT) {
                return new Endpoint($name);
            }
        );

        $authChecker = \Mockery::mock(AuthorizationCheckerInterface::class);
        $authChecker->allows('isGranted')->withArgs(['ROLE_ADMIN'])->andReturn(true);
        $authChecker->allows('isGranted')->withArgs(['ROLE_USER'])->andReturn(false);

        $resolver = new EndpointResolver($registry, $authChecker, $config);
        $endpoint = $resolver->resolveEndpoint($request);

        if ($expected) {
            self::assertEquals($expected, $endpoint->getName());
        } else {
            self::assertNull($endpoint);
        }
    }

    public function getData()
    {
        return [
            //get default
            [
                'default',
                [],
                Request::create('http://api.example.com'),
            ],
            //get admin by role
            [
                'admin',
                [
                    'endpoints' => [
                        'frontend' => [
                            'roles' => ['ROLE_USER'],
                        ],
                        'admin' => [
                            'roles' => ['ROLE_ADMIN'],
                        ],
                    ],
                ],
                Request::create('http://api.example.com'),
            ],
            //get admin by host
            [
                'admin',
                [
                    'endpoints' => [
                        'frontend' => [
                            'host' => 'frontend.example.com',
                        ],
                        'admin' => [
                            'host' => 'admin.example.com',
                        ],
                    ],
                ],
                Request::create('http://admin.example.com'),
            ],
            //get admin by path
            [
                'admin',
                [
                    'endpoints' => [
                        'frontend' => [
                            'path' => '/frontend',
                        ],
                        'admin' => [
                            'path' => '/admin',
                        ],
                    ],
                ],
                Request::create('http://example.com/admin'),
            ],
            //get none
            [
                null,
                [
                    'endpoints' => [
                        'frontend' => [
                            'host' => 'api.example.com',
                        ],
                        'admin' => [
                            'host' => 'api.admin.example.com',
                        ],
                    ],
                ],
                Request::create('http://example.com'),
            ],
        ];
    }
}
