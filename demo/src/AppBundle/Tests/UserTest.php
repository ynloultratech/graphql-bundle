<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\AppBundle\Tests;

use Ynlo\GraphQLBundle\Demo\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Demo\AppBundle\Entity\User;
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
            'users.users',
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
            ]
        );

        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals('Y3Vyc29yOjA=', 'data.users.users.pageInfo.startCursor');
        self::assertJsonPathEquals('Y3Vyc29yOjQ=', 'data.users.users.pageInfo.endCursor');
        self::assertJsonPathEquals(false, 'data.users.users.pageInfo.hasPreviousPage');
        self::assertJsonPathEquals(true, 'data.users.users.pageInfo.hasNextPage');

        self::assertJsonPathEquals('admin', 'data.users.users.edges[0].node.login');

        /** @var User $user1 */
        $user1 = self::getFixtureReference('user1');

        self::assertJsonArraySubset(['admin', $user1->getUsername()], 'data.users.users.edges[*].node.login');
        self::assertJsonPathEquals($user1->getProfile()->getPhone(), 'data.users.users.edges[1].node.profile.phone');
        self::assertJsonPathEquals(
            $user1->getProfile()->getAddress()->getZipCode(),
            'data.users.users.edges[1].node.profile.address.zipCode'
        );
    }

    /**
     * testUserList
     */
    public function testUserListWithOrder()
    {
        $records = self::getRepository(User::class)->findBy([], ['username' => 'DESC'], 3);
        self::query(
            'users.users',
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
            ]
        );

        self::assertJsonPathEquals('Y3Vyc29yOjA=', 'data.users.users.pageInfo.startCursor');
        self::assertJsonPathEquals('Y3Vyc29yOjI=', 'data.users.users.pageInfo.endCursor');
        self::assertJsonPathEquals(false, 'data.users.users.pageInfo.hasPreviousPage');
        self::assertJsonPathEquals(true, 'data.users.users.pageInfo.hasNextPage');

        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals($records[0]->getUsername(), 'data.users.users.edges[0].node.login');
        self::assertJsonPathEquals($records[1]->getUsername(), 'data.users.users.edges[1].node.login');
        self::assertJsonPathEquals($records[2]->getUsername(), 'data.users.users.edges[2].node.login');
    }

    /**
     * testUserListPaginationFirstAfter
     */
    public function testUserListPaginationFirstAfter()
    {
        $records = self::getRepository(User::class)->findBy([], ['username' => 'ASC'], 3, 3);

        self::query(
            'users.users',
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
            ]
        );

        self::assertJsonPathEquals(base64_encode('cursor:3'), 'data.users.users.pageInfo.startCursor');
        self::assertJsonPathEquals(base64_encode('cursor:5'), 'data.users.users.pageInfo.endCursor');
        self::assertJsonPathEquals(true, 'data.users.users.pageInfo.hasPreviousPage');
        self::assertJsonPathEquals(true, 'data.users.users.pageInfo.hasNextPage');

        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals($records[0]->getUsername(), 'data.users.users.edges[0].node.login');
        self::assertJsonPathEquals($records[1]->getUsername(), 'data.users.users.edges[1].node.login');
        self::assertJsonPathEquals($records[2]->getUsername(), 'data.users.users.edges[2].node.login');
    }

    /**
     * testUserListPaginationFirstBefore
     */
    public function testUserListPaginationFirstBefore()
    {
        $records = self::getRepository(User::class)->findBy([], ['username' => 'ASC'], 3, 0);

        self::query(
            'users.users',
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
            ]
        );

        self::assertJsonPathEquals(base64_encode('cursor:0'), 'data.users.users.pageInfo.startCursor');
        self::assertJsonPathEquals(base64_encode('cursor:2'), 'data.users.users.pageInfo.endCursor');
        self::assertJsonPathEquals(false, 'data.users.users.pageInfo.hasPreviousPage');
        self::assertJsonPathEquals(true, 'data.users.users.pageInfo.hasNextPage');

        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals($records[0]->getUsername(), 'data.users.users.edges[0].node.login');
        self::assertJsonPathEquals($records[1]->getUsername(), 'data.users.users.edges[1].node.login');
        self::assertJsonPathEquals($records[2]->getUsername(), 'data.users.users.edges[2].node.login');
    }

    /**
     * testUserListPaginationLastAfter
     */
    public function testUserListPaginationLastAfter()
    {
        $records = self::getRepository(User::class)->findBy([], ['username' => 'ASC'], 3, 8);

        self::query(
            'users.users',
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
            ]
        );

        self::assertJsonPathEquals(base64_encode('cursor:8'), 'data.users.users.pageInfo.startCursor');
        self::assertJsonPathEquals(base64_encode('cursor:10'), 'data.users.users.pageInfo.endCursor');
        self::assertJsonPathEquals(true, 'data.users.users.pageInfo.hasPreviousPage');
        self::assertJsonPathEquals(false, 'data.users.users.pageInfo.hasNextPage');

        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals($records[0]->getUsername(), 'data.users.users.edges[0].node.login');
        self::assertJsonPathEquals($records[1]->getUsername(), 'data.users.users.edges[1].node.login');
        self::assertJsonPathEquals($records[2]->getUsername(), 'data.users.users.edges[2].node.login');
    }

    /**
     * testUserListPaginationLastBefore
     */
    public function testUserListPaginationLastBefore()
    {
        $records = self::getRepository(User::class)->findBy([], ['username' => 'ASC'], 3, 2);

        self::query(
            'users.users',
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
            ]
        );

        self::assertJsonPathEquals(base64_encode('cursor:2'), 'data.users.users.pageInfo.startCursor');
        self::assertJsonPathEquals(base64_encode('cursor:4'), 'data.users.users.pageInfo.endCursor');
        self::assertJsonPathEquals(true, 'data.users.users.pageInfo.hasPreviousPage');
        self::assertJsonPathEquals(true, 'data.users.users.pageInfo.hasNextPage');

        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals($records[0]->getUsername(), 'data.users.users.edges[0].node.login');
        self::assertJsonPathEquals($records[1]->getUsername(), 'data.users.users.edges[1].node.login');
        self::assertJsonPathEquals($records[2]->getUsername(), 'data.users.users.edges[2].node.login');
    }

    /**
     * testUserGetPlural
     */
    public function testUserGetPlural()
    {
        /** @var User $user1 */
        $user1 = self::getFixtureReference('user1');

        self::query(
            'users.byLogin',
            ['logins' => ['admin', $user1->getUsername()]],
            [
                'id',
                'login',
            ]
        );

        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals('admin', 'data.users.byLogin[0].login');
        self::assertJsonPathEquals($user1->getUsername(), 'data.users.byLogin[1].login');

        //The order of logins should be equal to response
        self::query(
            'users.byLogin',
            ['logins' => [$user1->getUsername(), 'admin']],
            [
                'id',
                'login',
            ]
        );

        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals($user1->getUsername(), 'data.users.byLogin[0].login');
        self::assertJsonPathEquals('admin', 'data.users.byLogin[1].login');
    }

    /**
     * testAddUser
     */
    public function testAddUser()
    {
        self::mutation(
            'users.add',
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
        self::assertJsonPathEquals($clientMutationId, 'data.users.add.clientMutationId');
        self::assertRepositoryContains(User::class, ['username' => $login]);
        $id = self::getJsonPathValue('data.users.add.node.id');
        $loginInResponse = self::getJsonPathValue('data.users.add.node.login');
        self::assertEquals($login, $loginInResponse);

        /** @var User $createdUser */
        $createdUser = self::findOneById(User::class, $id);

        self::assertEquals($login, $createdUser->getUsername());

        self::assertJsonPathEquals($email, 'data.users.add.node.profile.email');
    }

    /**
     * testAddUserValidation
     */
    public function testAddUserValidation()
    {
        self::mutation(
            'users.add',
            [
                'input' => [
                    'login' => '',
                    'profile' => [
                        'email' => 'sssss',
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
                'constraintViolations' => [
                    'message',
                    'propertyPath',
                ],
            ]
        );

        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals($clientMutationId, 'data.users.add.clientMutationId');
        self::assertJsonPathNull('data.users.add.node');
        self::assertJsonPathEquals('This value should not be blank.', 'data.users.add.constraintViolations[0].message');
        self::assertJsonPathEquals('login', 'data.users.add.constraintViolations[0].propertyPath');
        self::assertJsonPathEquals('This value is not a valid email address.', 'data.users.add.constraintViolations[1].message');
        self::assertJsonPathEquals('profile.email', 'data.users.add.constraintViolations[1].propertyPath');
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
            'users.update',
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
        $loginInResponse = self::getJsonPathValue('data.users.update.node.login');
        self::assertEquals($newLogin, $loginInResponse);

        self::assertJsonPathEquals($email, 'data.users.update.node.profile.email');
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
            'users.delete',
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
        self::assertJsonPathEquals($id, 'data.users.delete.id');
        self::assertJsonPathEquals($clientMutationId, 'data.users.delete.clientMutationId');
    }

    /**
     * testUserList
     */
    public function testGetPostsInsideUser()
    {
        /** @var User $user1 */
        $user1 = self::getFixtureReference('user1');
        self::query(
            'node',
            ['id' => self::encodeID('User', $user1->getId())],
            [
                '... on User' => [
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
            self::assertJsonPathEquals($post->getTitle(), "data.node.posts.edges[$index].node.title");
        }
    }
}
