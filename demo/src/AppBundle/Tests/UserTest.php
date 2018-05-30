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

use Ynlo\GraphQLBundle\Demo\AppBundle\DataFixtures\ORM\Fixtures;
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
        $query = <<<'GraphQL'
query {
    users{
        all(first:5){
            totalCount
            pageInfo {
                endCursor
                startCursor
                hasPreviousPage
                hasNextPage
            }
            edges {
                node {
                    id
                    login
                    profile {
                        phone
                        address {
                            zipCode
                        }
                    }
                }
            }
        }
    }
}
GraphQL;
        self::send($query);

        self::assertResponseCodeIsOK();
        self::assertResponseJsonValueEquals('Y3Vyc29yOjA=', 'data.users.all.pageInfo.startCursor');
        self::assertResponseJsonValueEquals('Y3Vyc29yOjQ=', 'data.users.all.pageInfo.endCursor');
        self::assertResponseJsonValueEquals(false, 'data.users.all.pageInfo.hasPreviousPage');
        self::assertResponseJsonValueEquals(true, 'data.users.all.pageInfo.hasNextPage');

        self::assertResponseJsonValueEquals('admin', 'data.users.all.edges[0].node.login');

        /** @var User $user1 */
        $user1 = self::getFixtureReference('user1');

        self::assertResponseJsonArraySubset(['admin', $user1->getUsername()], 'data.users.all.edges[*].node.login');
        self::assertResponseJsonValueEquals($user1->getProfile()->getPhone(), 'data.users.all.edges[1].node.profile.phone');
        self::assertResponseJsonValueEquals(
            $user1->getProfile()->getAddress()->getZipCode(),
            'data.users.all.edges[1].node.profile.address.zipCode'
        );
    }

    /**
     * testUserList
     */
    public function testUserListWithOrder()
    {
        $records = self::getRepository(User::class)->findBy([], ['username' => 'DESC'], 3);

        $query = <<<'GraphQL'
query {
    users{
        all(first:3, orderBy: {field:"login", direction: DESC}){
            totalCount
            pageInfo {
                endCursor
                startCursor
                hasPreviousPage
                hasNextPage
            }
            edges {
                node {
                    id
                    login
                    profile {
                        phone
                        address {
                            zipCode
                        }
                    }
                }
            }
        }
    }
}
GraphQL;
        self::send($query);

        self::assertResponseJsonValueEquals('Y3Vyc29yOjA=', 'data.users.all.pageInfo.startCursor');
        self::assertResponseJsonValueEquals('Y3Vyc29yOjI=', 'data.users.all.pageInfo.endCursor');
        self::assertResponseJsonValueEquals(false, 'data.users.all.pageInfo.hasPreviousPage');
        self::assertResponseJsonValueEquals(true, 'data.users.all.pageInfo.hasNextPage');

        self::assertResponseCodeIsOK();
        self::assertResponseJsonValueEquals($records[0]->getUsername(), 'data.users.all.edges[0].node.login');
        self::assertResponseJsonValueEquals($records[1]->getUsername(), 'data.users.all.edges[1].node.login');
        self::assertResponseJsonValueEquals($records[2]->getUsername(), 'data.users.all.edges[2].node.login');
    }

    /**
     * testUserListPaginationFirstAfter
     */
    public function testUserListPaginationFirstAfter()
    {
        $records = self::getRepository(User::class)->findBy([], ['username' => 'ASC'], 3, 3);
        $query = <<<'GraphQL'
query ($cursor: String){
    users{
        all(first:3, orderBy: {field:"login", direction: ASC}, after: $cursor){
            totalCount
            pageInfo {
                endCursor
                startCursor
                hasPreviousPage
                hasNextPage
            }
            edges {
                node {
                    id
                    login
                    profile {
                        phone
                        address {
                            zipCode
                        }
                    }
                }
            }
        }
    }
}
GraphQL;
        self::send($query, ['cursor' => base64_encode('cursor:2')]);

        self::assertResponseJsonValueEquals(base64_encode('cursor:3'), 'data.users.all.pageInfo.startCursor');
        self::assertResponseJsonValueEquals(base64_encode('cursor:5'), 'data.users.all.pageInfo.endCursor');
        self::assertResponseJsonValueEquals(true, 'data.users.all.pageInfo.hasPreviousPage');
        self::assertResponseJsonValueEquals(true, 'data.users.all.pageInfo.hasNextPage');

        self::assertResponseCodeIsOK();
        self::assertResponseJsonValueEquals($records[0]->getUsername(), 'data.users.all.edges[0].node.login');
        self::assertResponseJsonValueEquals($records[1]->getUsername(), 'data.users.all.edges[1].node.login');
        self::assertResponseJsonValueEquals($records[2]->getUsername(), 'data.users.all.edges[2].node.login');
    }

    /**
     * testUserListPaginationFirstBefore
     */
    public function testUserListPaginationFirstBefore()
    {
        $records = self::getRepository(User::class)->findBy([], ['username' => 'ASC'], 3, 0);

        $query = <<<'GraphQL'
query ($cursor: String){
    users{
        all(first:3, orderBy: {field:"login", direction: ASC}, before: $cursor){
            totalCount
            pageInfo {
                endCursor
                startCursor
                hasPreviousPage
                hasNextPage
            }
            edges {
                node {
                    id
                    login
                    profile {
                        phone
                        address {
                            zipCode
                        }
                    }
                }
            }
        }
    }
}
GraphQL;
        self::send($query, ['cursor' => base64_encode('cursor:7')]);

        self::assertResponseJsonValueEquals(base64_encode('cursor:0'), 'data.users.all.pageInfo.startCursor');
        self::assertResponseJsonValueEquals(base64_encode('cursor:2'), 'data.users.all.pageInfo.endCursor');
        self::assertResponseJsonValueEquals(false, 'data.users.all.pageInfo.hasPreviousPage');
        self::assertResponseJsonValueEquals(true, 'data.users.all.pageInfo.hasNextPage');

        self::assertResponseCodeIsOK();
        self::assertResponseJsonValueEquals($records[0]->getUsername(), 'data.users.all.edges[0].node.login');
        self::assertResponseJsonValueEquals($records[1]->getUsername(), 'data.users.all.edges[1].node.login');
        self::assertResponseJsonValueEquals($records[2]->getUsername(), 'data.users.all.edges[2].node.login');
    }

    /**
     * testUserListPaginationLastAfter
     */
    public function testUserListPaginationLastAfter()
    {
        $records = self::getRepository(User::class)->findBy([], ['username' => 'ASC'], 3, 8);

        $query = <<<'GraphQL'
query ($cursor: String){
    users{
        all(last:3, orderBy: {field:"login", direction: ASC}, after: $cursor){
            totalCount
            pageInfo {
                endCursor
                startCursor
                hasPreviousPage
                hasNextPage
            }
            edges {
                node {
                    id
                    login
                    profile {
                        phone
                        address {
                            zipCode
                        }
                    }
                }
            }
        }
    }
}
GraphQL;
        self::send($query, ['cursor' => base64_encode('cursor:5')]);

        self::assertResponseJsonValueEquals(base64_encode('cursor:8'), 'data.users.all.pageInfo.startCursor');
        self::assertResponseJsonValueEquals(base64_encode('cursor:10'), 'data.users.all.pageInfo.endCursor');
        self::assertResponseJsonValueEquals(true, 'data.users.all.pageInfo.hasPreviousPage');
        self::assertResponseJsonValueEquals(false, 'data.users.all.pageInfo.hasNextPage');

        self::assertResponseCodeIsOK();
        self::assertResponseJsonValueEquals($records[0]->getUsername(), 'data.users.all.edges[0].node.login');
        self::assertResponseJsonValueEquals($records[1]->getUsername(), 'data.users.all.edges[1].node.login');
        self::assertResponseJsonValueEquals($records[2]->getUsername(), 'data.users.all.edges[2].node.login');
    }

    /**
     * testUserListPaginationLastBefore
     */
    public function testUserListPaginationLastBefore()
    {
        $records = self::getRepository(User::class)->findBy([], ['username' => 'ASC'], 3, 2);

        $query = <<<'GraphQL'
query ($cursor: String){
    users{
        all(last:3, orderBy: {field:"login", direction: ASC}, before: $cursor){
            totalCount
            pageInfo {
                endCursor
                startCursor
                hasPreviousPage
                hasNextPage
            }
            edges {
                node {
                    id
                    login
                    profile {
                        phone
                        address {
                            zipCode
                        }
                    }
                }
            }
        }
    }
}
GraphQL;
        self::send($query, ['cursor' => base64_encode('cursor:5')]);

        self::assertResponseJsonValueEquals(base64_encode('cursor:2'), 'data.users.all.pageInfo.startCursor');
        self::assertResponseJsonValueEquals(base64_encode('cursor:4'), 'data.users.all.pageInfo.endCursor');
        self::assertResponseJsonValueEquals(true, 'data.users.all.pageInfo.hasPreviousPage');
        self::assertResponseJsonValueEquals(true, 'data.users.all.pageInfo.hasNextPage');

        self::assertResponseCodeIsOK();
        self::assertResponseJsonValueEquals($records[0]->getUsername(), 'data.users.all.edges[0].node.login');
        self::assertResponseJsonValueEquals($records[1]->getUsername(), 'data.users.all.edges[1].node.login');
        self::assertResponseJsonValueEquals($records[2]->getUsername(), 'data.users.all.edges[2].node.login');
    }

    /**
     * testUserGetPlural
     */
    public function testUserGetPlural()
    {
        /** @var User $user1 */
        $user1 = self::getFixtureReference('user1');

        $query = <<<'GraphQL'
query($user1:String!){
    users {
        byLogin(logins: ["admin", $user1]){
            id
            login
        }
    }
}
GraphQL;
        self::send(
            $query,
            [
                'user1' => $user1->getUsername(),
            ]
        );

        self::assertResponseCodeIsOK();
        self::assertResponseJsonValueEquals('admin', 'data.users.byLogin[0].login');
        self::assertResponseJsonValueEquals($user1->getUsername(), 'data.users.byLogin[1].login');

        $query = <<<'GraphQL'
query($user1:String!){
    users {
        byLogin(logins: [$user1, "admin"]){
            id
            login
        }
    }
}
GraphQL;
        self::send(
            $query,
            [
                'user1' => $user1->getUsername(),
            ]
        );

        self::assertResponseCodeIsOK();
        self::assertResponseJsonValueEquals($user1->getUsername(), 'data.users.byLogin[0].login');
        self::assertResponseJsonValueEquals('admin', 'data.users.byLogin[1].login');
    }

    /**
     * testAddUser
     */
    public function testAddUser()
    {
        $mutation = <<<'GraphQL'
mutation($input: AddUserInput!){
    users {
        add(input: $input ) {
            node {
                id
                login
                email
                profile {
                    email
                }
            }
            clientMutationId
        }
    }
}
GraphQL;

        self::send(
            $mutation,
            [
                'input' => [
                    'login' => $login = 'graphql',
                    'email' => $email = 'test@example.com',
                    'password' => Fixtures::DEFAULT_PASSWORD,
                    'profile' => [
                        'email' => $email,
                    ],
                    'clientMutationId' => (string) $clientMutationId = mt_rand(),
                ],
            ]
        );

        self::assertResponseCodeIsOK();
        self::assertResponseJsonValueEquals($clientMutationId, 'data.users.add.clientMutationId');
        self::assertRepositoryContains(User::class, ['username' => $login]);
        $id = self::getResponseJsonPathValue('data.users.add.node.id');
        $loginInResponse = self::getResponseJsonPathValue('data.users.add.node.login');
        self::assertEquals($login, $loginInResponse);

        /** @var User $createdUser */
        $createdUser = self::findOneByGlobalId($id);

        self::assertEquals($login, $createdUser->getUsername());

        self::assertResponseJsonValueEquals($email, 'data.users.add.node.email');
        self::assertResponseJsonValueEquals($email, 'data.users.add.node.profile.email');
    }

    /**
     * testAddUserValidation
     */
    public function testAddUserValidation()
    {
        $mutation = <<<'GraphQL'
mutation($input: AddUserInput!){
    users {
        add(input: $input ) {
            node {
                id
                login
                profile {
                    email
                }
            }
            clientMutationId
            constraintViolations {
                message
                propertyPath
            }
        }
    }
}
GraphQL;

        self::send(
            $mutation,
            [
                'input' => [
                    'login' => '',
                    'password' => '',
                    'email' => '',
                    'profile' => [
                        'email' => 'sssss',
                    ],
                    'clientMutationId' => (string) $clientMutationId = mt_rand(),
                ],
            ]
        );

        self::assertResponseCodeIsOK();
        self::assertResponseJsonValueEquals($clientMutationId, 'data.users.add.clientMutationId');
        self::assertResponseJsonValueIsNull('data.users.add.node');
        self::assertResponseJsonValueEquals('This value should not be blank.', 'data.users.add.constraintViolations[0].message');
        self::assertResponseJsonValueEquals('login', 'data.users.add.constraintViolations[0].propertyPath');
        self::assertResponseJsonValueEquals('This value is not a valid email address.', 'data.users.add.constraintViolations[1].message');
        self::assertResponseJsonValueEquals('profile.email', 'data.users.add.constraintViolations[1].propertyPath');
    }

    /**
     * testUpdateUser
     */
    public function testUpdateUser()
    {
        $newLogin = 'graphql';

        /** @var User $user1 */
        $user1 = self::getFixtureReference('user1');
        self::assertRepositoryContains(User::class, ['username' => $user1->getUsername(), 'enabled' => true]);
        self::assertRepositoryNotContains(User::class, ['username' => $newLogin]);

        $mutation = <<<'GraphQL'
mutation($input: UpdateUserInput!){
    users {
        update(input: $input ) {
            node {
                id
                login
                enabled
                profile {
                    email
                }
            }
            clientMutationId
            constraintViolations {
                message
                propertyPath
            }
        }
    }
}
GraphQL;

        self::send(
            $mutation,
            [
                'input' => [
                    'id' => $id = self::encodeID($user1),
                    'login' => $newLogin,
                    'enabled' => false,
                    'profile' => [
                        'email' => $email = 'test@example.com',
                    ],
                    'clientMutationId' => (string) $clientMutationId = mt_rand(),
                ],
            ]
        );

        self::assertResponseCodeIsOK();
        self::assertRepositoryContains(User::class, ['username' => $newLogin, 'enabled' => false]);
        $loginInResponse = self::getResponseJsonPathValue('data.users.update.node.login');
        self::assertEquals($newLogin, $loginInResponse);

        self::assertResponseJsonValueEquals($email, 'data.users.update.node.profile.email');
        self::assertResponseJsonValueIsFalse('data.users.update.node.enabled');
    }

    /**
     * testDeleteUser
     */
    public function testDeleteUser()
    {
        /** @var User $user1 */
        $user1 = self::getFixtureReference('user1');
        self::assertRepositoryContains(User::class, ['username' => $user1->getUsername()]);

        $mutation = <<<'GraphQL'
mutation($input: DeleteUserInput!){
    users {
        delete(input: $input ) {
            id
            clientMutationId
        }
    }
}
GraphQL;

        self::send(
            $mutation,
            [
                'input' => [
                    'id' => $id = self::encodeID($user1),
                    'clientMutationId' => (string) $clientMutationId = mt_rand(),
                ],
            ]
        );

        self::assertResponseCodeIsOK();
        self::assertRepositoryNotContains(User::class, ['username' => $user1->getUsername()]);
        self::assertResponseJsonValueEquals($id, 'data.users.delete.id');
        self::assertResponseJsonValueEquals($clientMutationId, 'data.users.delete.clientMutationId');
    }

    /**
     * testUserList
     */
    public function testGetPostsInsideUser()
    {
        /** @var User $user1 */
        $user1 = self::getFixtureReference('user1');

        $query = <<<'GraphQL'
query($id: ID!){
    node(id: $id) {
        ... on User {
            id
            login
            posts(first: 10){
                totalCount
                pageInfo {
                    endCursor
                    startCursor
                    hasPreviousPage
                    hasNextPage
                }
                edges {
                    node {
                        title
                    }
                }
            }
        }
    }
}
GraphQL;

        self::send(
            $query,
            [
                'id' => self::encodeID($user1),
            ]
        );

        self::assertResponseCodeIsOK();
        /** @var Post $post */
        foreach ($user1->getPosts() as $index => $post) {
            self::assertResponseJsonValueEquals($post->getTitle(), "data.node.posts.edges[$index].node.title");
        }
    }
}
