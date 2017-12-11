<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Tests;

use Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity\Post;
use Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity\User;
use Ynlo\GraphQLBundle\Test\ApiTestCase;

/**
 * Class UserTest
 */
class UserTest extends ApiTestCase
{
    /**
     * testUserList
     */
    public function testUserList()
    {
        self::query(
            'allUsers',
            ['first' => 5],
            [
                'totalCount',
                'pageInfo' => [
                    'endCursor',
                    'startCursor',
                    'hasPreviousPage',
                    'hasNextPage',
                ],
                'edges' => [
                    'node' => [
                        '... on User' => [
                            'id',
                            'login',
                            'profile' => [
                                'phone',
                                'address' => [
                                    'zipCode',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals('Y3Vyc29yOjA=', 'data.allUsers.pageInfo.startCursor');
        self::assertJsonPathEquals('Y3Vyc29yOjQ=', 'data.allUsers.pageInfo.endCursor');
        self::assertJsonPathEquals(false, 'data.allUsers.pageInfo.hasPreviousPage');
        self::assertJsonPathEquals(true, 'data.allUsers.pageInfo.hasNextPage');

        self::assertJsonPathEquals('admin', 'data.allUsers.edges[0].node.login');

        /** @var User $user1 */
        $user1 = self::getFixtureReference('user1');

        self::assertJsonArraySubset(['admin', $user1->getUsername()], 'data.allUsers.edges[*].node.login');
        self::assertJsonPathEquals($user1->getProfile()->getPhone(), 'data.allUsers.edges[1].node.profile.phone');
        self::assertJsonPathEquals(
            $user1->getProfile()->getAddress()->getZipCode(),
            'data.allUsers.edges[1].node.profile.address.zipCode'
        );
    }

    /**
     * testUserList
     */
    public function testUserListWithOrder()
    {
        $records = self::getRepository(User::class)->findBy([], ['username' => 'DESC'], 3);
        self::query(
            'allUsers',
            ['first' => 3, 'orderBy' => ['field' => 'login', 'direction' => 'DESC']],
            [
                'totalCount',
                'pageInfo' => [
                    'endCursor',
                    'startCursor',
                    'hasPreviousPage',
                    'hasNextPage',
                ],
                'edges' => [
                    'node' => [
                        '... on User' => [
                            'id',
                            'login',
                            'profile' => [
                                'phone',
                                'address' => [
                                    'zipCode',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        self::assertJsonPathEquals('Y3Vyc29yOjA=', 'data.allUsers.pageInfo.startCursor');
        self::assertJsonPathEquals('Y3Vyc29yOjI=', 'data.allUsers.pageInfo.endCursor');
        self::assertJsonPathEquals(false, 'data.allUsers.pageInfo.hasPreviousPage');
        self::assertJsonPathEquals(true, 'data.allUsers.pageInfo.hasNextPage');

        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals($records[0]->getUsername(), 'data.allUsers.edges[0].node.login');
        self::assertJsonPathEquals($records[1]->getUsername(), 'data.allUsers.edges[1].node.login');
        self::assertJsonPathEquals($records[2]->getUsername(), 'data.allUsers.edges[2].node.login');
    }

    /**
     * testUserListPaginationFirstAfter
     */
    public function testUserListPaginationFirstAfter()
    {
        $records = self::getRepository(User::class)->findBy([], ['username' => 'ASC'], 3, 3);

        self::query(
            'allUsers',
            ['first' => 3, 'orderBy' => ['field' => 'login', 'direction' => 'ASC'], 'after' => base64_encode('cursor:2')],
            [
                'totalCount',
                'pageInfo' => [
                    'endCursor',
                    'startCursor',
                    'hasPreviousPage',
                    'hasNextPage',
                ],
                'edges' => [
                    'node' => [
                        '... on User' => [
                            'id',
                            'login',
                            'profile' => [
                                'phone',
                                'address' => [
                                    'zipCode',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        self::assertJsonPathEquals(base64_encode('cursor:3'), 'data.allUsers.pageInfo.startCursor');
        self::assertJsonPathEquals(base64_encode('cursor:5'), 'data.allUsers.pageInfo.endCursor');
        self::assertJsonPathEquals(true, 'data.allUsers.pageInfo.hasPreviousPage');
        self::assertJsonPathEquals(true, 'data.allUsers.pageInfo.hasNextPage');

        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals($records[0]->getUsername(), 'data.allUsers.edges[0].node.login');
        self::assertJsonPathEquals($records[1]->getUsername(), 'data.allUsers.edges[1].node.login');
        self::assertJsonPathEquals($records[2]->getUsername(), 'data.allUsers.edges[2].node.login');
    }

    /**
     * testUserListPaginationFirstBefore
     */
    public function testUserListPaginationFirstBefore()
    {
        $records = self::getRepository(User::class)->findBy([], ['username' => 'ASC'], 3, 0);

        self::query(
            'allUsers',
            ['first' => 3, 'orderBy' => ['field' => 'login', 'direction' => 'ASC'], 'before' => base64_encode('cursor:7')],
            [
                'totalCount',
                'pageInfo' => [
                    'endCursor',
                    'startCursor',
                    'hasPreviousPage',
                    'hasNextPage',
                ],
                'edges' => [
                    'node' => [
                        '... on User' => [
                            'id',
                            'login',
                            'profile' => [
                                'phone',
                                'address' => [
                                    'zipCode',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        self::assertJsonPathEquals(base64_encode('cursor:0'), 'data.allUsers.pageInfo.startCursor');
        self::assertJsonPathEquals(base64_encode('cursor:2'), 'data.allUsers.pageInfo.endCursor');
        self::assertJsonPathEquals(false, 'data.allUsers.pageInfo.hasPreviousPage');
        self::assertJsonPathEquals(true, 'data.allUsers.pageInfo.hasNextPage');

        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals($records[0]->getUsername(), 'data.allUsers.edges[0].node.login');
        self::assertJsonPathEquals($records[1]->getUsername(), 'data.allUsers.edges[1].node.login');
        self::assertJsonPathEquals($records[2]->getUsername(), 'data.allUsers.edges[2].node.login');
    }

    /**
     * testUserListPaginationLastAfter
     */
    public function testUserListPaginationLastAfter()
    {
        $records = self::getRepository(User::class)->findBy([], ['username' => 'ASC'], 3, 8);

        self::query(
            'allUsers',
            ['last' => 3, 'orderBy' => ['field' => 'login', 'direction' => 'ASC'], 'after' => base64_encode('cursor:5')],
            [
                'totalCount',
                'pageInfo' => [
                    'endCursor',
                    'startCursor',
                    'hasPreviousPage',
                    'hasNextPage',
                ],
                'edges' => [
                    'node' => [
                        '... on User' => [
                            'id',
                            'login',
                            'profile' => [
                                'phone',
                                'address' => [
                                    'zipCode',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        self::assertJsonPathEquals(base64_encode('cursor:8'), 'data.allUsers.pageInfo.startCursor');
        self::assertJsonPathEquals(base64_encode('cursor:10'), 'data.allUsers.pageInfo.endCursor');
        self::assertJsonPathEquals(true, 'data.allUsers.pageInfo.hasPreviousPage');
        self::assertJsonPathEquals(false, 'data.allUsers.pageInfo.hasNextPage');

        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals($records[0]->getUsername(), 'data.allUsers.edges[0].node.login');
        self::assertJsonPathEquals($records[1]->getUsername(), 'data.allUsers.edges[1].node.login');
        self::assertJsonPathEquals($records[2]->getUsername(), 'data.allUsers.edges[2].node.login');
    }

    /**
     * testUserListPaginationLastBefore
     */
    public function testUserListPaginationLastBefore()
    {
        $records = self::getRepository(User::class)->findBy([], ['username' => 'ASC'], 3, 2);

        self::query(
            'allUsers',
            ['last' => 3, 'orderBy' => ['field' => 'login', 'direction' => 'ASC'], 'before' => base64_encode('cursor:5')],
            [
                'totalCount',
                'pageInfo' => [
                    'endCursor',
                    'startCursor',
                    'hasPreviousPage',
                    'hasNextPage',
                ],
                'edges' => [
                    'node' => [
                        '... on User' => [
                            'id',
                            'login',
                            'profile' => [
                                'phone',
                                'address' => [
                                    'zipCode',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        self::assertJsonPathEquals(base64_encode('cursor:2'), 'data.allUsers.pageInfo.startCursor');
        self::assertJsonPathEquals(base64_encode('cursor:4'), 'data.allUsers.pageInfo.endCursor');
        self::assertJsonPathEquals(true, 'data.allUsers.pageInfo.hasPreviousPage');
        self::assertJsonPathEquals(true, 'data.allUsers.pageInfo.hasNextPage');

        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals($records[0]->getUsername(), 'data.allUsers.edges[0].node.login');
        self::assertJsonPathEquals($records[1]->getUsername(), 'data.allUsers.edges[1].node.login');
        self::assertJsonPathEquals($records[2]->getUsername(), 'data.allUsers.edges[2].node.login');
    }

    /**
     * testUserGet
     */
    public function testUserGet()
    {
        self::query(
            'user',
            ['login' => 'admin'],
            [
                'id',
                'login',
            ]
        );
        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals('admin', 'data.user.login');
    }

    /**
     * testUserGetPlural
     */
    public function testUserGetPlural()
    {
        /** @var User $user1 */
        $user1 = self::getFixtureReference('user1');

        self::query(
            'users',
            ['logins' => ['admin', $user1->getUsername()]],
            [
                'id',
                'login',
            ]
        );

        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals('admin', 'data.users[0].login');
        self::assertJsonPathEquals($user1->getUsername(), 'data.users[1].login');

        //The order of logins should be equal to response
        self::query(
            'users',
            ['logins' => [$user1->getUsername(), 'admin']],
            [
                'id',
                'login',
            ]
        );

        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals($user1->getUsername(), 'data.users[0].login');
        self::assertJsonPathEquals('admin', 'data.users[1].login');
    }

    /**
     * testAddUser
     */
    public function testAddUser()
    {
        self::mutation(
            'addUser',
            [
                'input' => [
                    'login' => $login = 'graphql',
                    'profile' => [
                        'email' => $email = 'test@example.com',
                    ],
                    'clientMutationId' => (string) $clientMutationId = mt_rand(),
                ],
            ],
            [
                'node' => [
                    '... on User' => [
                        'id',
                        'login',
                        'profile' => [
                            'email',
                        ],
                    ],
                ],
                'clientMutationId',
            ]
        );

        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals($clientMutationId, 'data.addUser.clientMutationId');
        self::assertRepositoryContains(User::class, ['username' => $login]);
        $id = self::getJsonPathValue('data.addUser.node.id');
        $loginInResponse = self::getJsonPathValue('data.addUser.node.login');
        self::assertEquals($login, $loginInResponse);

        /** @var User $createdUser */
        $createdUser = self::findOneById(User::class, $id);

        self::assertEquals($login, $createdUser->getUsername());

        self::assertJsonPathEquals($email, 'data.addUser.node.profile.email');
    }

    /**
     * testAddUserDryRun
     */
    public function testAddUserDryRun()
    {
        self::mutation(
            'addUser',
            [
                'input' => [
                    'login' => $login = 'graphql',
                    'profile' => [
                        'email' => $email = 'test@example.com',
                    ],
                    'dryRun' => true,
                    'clientMutationId' => (string) $clientMutationId = mt_rand(),
                ],
            ],
            [
                'node' => [
                    '... on User' => [
                        'id',
                        'login',
                        'profile' => [
                            'email',
                        ],
                    ],
                ],
                'clientMutationId',
                'constraintViolations' => [
                    'message',
                ],
            ]
        );

        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals($clientMutationId, 'data.addUser.clientMutationId');
        self::assertJsonPathEquals([], 'data.addUser.constraintViolations');
        self::assertRepositoryNotContains(User::class, ['username' => $login]);
    }

    /**
     * testAddUserValidation
     */
    public function testAddUserValidation()
    {
        self::mutation(
            'addUser',
            [
                'input' => [
                    'login' => '',
                    'profile' => [
                        'email' => 'sssss',
                    ],
                    'dryRun' => true,
                    'clientMutationId' => (string) $clientMutationId = mt_rand(),
                ],
            ],
            [
                'node' => [
                    '... on User' => [
                        'id',
                        'login',
                        'profile' => [
                            'email',
                        ],
                    ],
                ],
                'clientMutationId',
                'constraintViolations' => [
                    'message',
                    'propertyPath',
                ],
            ]
        );

        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals($clientMutationId, 'data.addUser.clientMutationId');
        self::assertJsonPathNull('data.addUser.node');
        self::assertJsonPathEquals('This value should not be blank.', 'data.addUser.constraintViolations[0].message');
        self::assertJsonPathEquals('login', 'data.addUser.constraintViolations[0].propertyPath');
        self::assertJsonPathEquals('This value is not a valid email address.', 'data.addUser.constraintViolations[1].message');
        self::assertJsonPathEquals('profile.email', 'data.addUser.constraintViolations[1].propertyPath');
    }

    /**
     * testUpdateUser
     */
    public function testUpdateUser()
    {
        $newLogin = 'graphql';

        /** @var User $user1 */
        $user1 = self::getFixtureReference('user1');
        self::assertRepositoryContains(User::class, ['username' => $user1->getUsername()]);
        self::assertRepositoryNotContains(User::class, ['username' => $newLogin]);

        self::mutation(
            'updateUser',
            [
                'input' => [
                    'id' => $id = self::encodeID('User', $user1->getId()),
                    'login' => $newLogin,
                    'profile' => [
                        'email' => $email = 'test@example.com',
                    ],
                    'clientMutationId' => (string) $clientMutationId = mt_rand(),
                ],
            ],
            [
                'node' => [
                    '... on User' => [
                        'id',
                        'login',
                        'profile' => [
                            'email',
                        ],
                    ],
                ],
                'constraintViolations' => [
                    'message',
                ],
            ]
        );

        self::assertResponseCodeIsOK();
        self::assertRepositoryContains(User::class, ['username' => $newLogin]);
        $loginInResponse = self::getJsonPathValue('data.updateUser.node.login');
        self::assertEquals($newLogin, $loginInResponse);

        self::assertJsonPathEquals($email, 'data.updateUser.node.profile.email');
    }

    /**
     * testDeleteUser
     */
    public function testDeleteUser()
    {
        /** @var User $user1 */
        $user1 = self::getFixtureReference('user1');
        self::assertRepositoryContains(User::class, ['username' => $user1->getUsername()]);

        self::mutation(
            'deleteUser',
            [
                'input' => [
                    'id' => $id = self::encodeID('User', $user1->getId()),
                    'clientMutationId' => (string) $clientMutationId = mt_rand(),
                ],
            ],
            [
                'id',
                'clientMutationId',
            ]
        );

        self::assertResponseCodeIsOK();
        self::assertRepositoryNotContains(User::class, ['username' => $user1->getUsername()]);
        self::assertJsonPathEquals($id, 'data.deleteUser.id');
        self::assertJsonPathEquals($clientMutationId, 'data.deleteUser.clientMutationId');
    }

    /**
     * testUserList
     */
    public function testGetPostsInsideUser()
    {
        /** @var User $user1 */
        $user1 = self::getFixtureReference('user1');
        self::query(
            'user',
            ['login' => $user1->getUsername()],
            [
                'id',
                'login',
                'posts' => [
                    ['first' => 10],
                    [
                        'totalCount',
                        'pageInfo' => [
                            'endCursor',
                            'startCursor',
                            'hasPreviousPage',
                            'hasNextPage',
                        ],
                        'edges' => [
                            'node' => [
                                '... on Post' => [
                                    'title',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        self::assertResponseCodeIsOK();
        /** @var Post $post */
        foreach ($user1->getPosts() as $index => $post) {
            self::assertJsonPathEquals($post->getTitle(), "data.user.posts.edges[$index].node.title");
        }
    }
}
