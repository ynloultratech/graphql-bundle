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
            'userList',
            [
                'id',
                'login',
                'profile' => [
                    'phone',
                    'address' => [
                        'zipCode',
                    ],
                ],
            ]
        );
        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals('admin', 'data.userList[0].login');

        /** @var User $user1 */
        $user1 = self::getFixtureReference('user1');

        self::assertJsonArraySubset(['admin', $user1->getUsername()], 'data.userList[*].login');
        self::assertJsonPathEquals($user1->getProfile()->getPhone(), 'data.userList[1].profile.phone');
        self::assertJsonPathEquals(
            $user1->getProfile()->getAddress()->getZipCode(),
            'data.userList[1].profile.address.zipCode'
        );
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
                        'email' => '',
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
        self::assertJsonPathEquals('This value should not be blank.', 'data.addUser.constraintViolations[1].message');
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
     * testRemoveUser
     */
    public function testRemoveUser()
    {
        /** @var User $user1 */
        $user1 = self::getFixtureReference('user1');
        self::assertRepositoryContains(User::class, ['username' => $user1->getUsername()]);

        self::mutation(
            'removeUser',
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
        self::assertJsonPathEquals($id, 'data.removeUser.id');
        self::assertJsonPathEquals($clientMutationId, 'data.removeUser.clientMutationId');
    }
}
