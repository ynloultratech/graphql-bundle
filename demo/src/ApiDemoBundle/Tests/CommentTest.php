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

use Faker\Factory;
use Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity\Post;
use Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity\PostComment;
use Ynlo\GraphQLBundle\Test\ApiTestCase;

/**
 * Class CommentTest
 */
class CommentTest extends ApiTestCase
{
    /**
     * @return mixed|null
     */
    public function testAddComment()
    {
        //disabled test temporarily
        self::assertTrue(true);

        return;

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

        $faker = Factory::create();

        /** @var Post $post */
        $post = self::getFixtureReference('post1');

        self::mutation(
            'addComment',
            [
                'input' => [
                    'commentableId' => $commentableId = self::encodeID('Post', $post->getId()),
                    'body' => $comment = $faker->sentence,
                    'clientMutationId' => (string) $clientMutationId = mt_rand(),
                ],
            ],
            [
                'node' => [
                    '... on PostComment' => [
                        'id',
                        'body',
                        'commentable' => [
                            '... on Post' => [
                                'title',
                            ],
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

        self::assertRepositoryContains(PostComment::class, ['body' => $comment, 'post' => $post]);
        self::assertJsonPathEquals($comment, 'data.addComment.node.body');
        self::assertJsonPathEquals($post->getTitle(), 'data.addComment.node.commentable.title');
        self::assertJsonPathEquals($clientMutationId, 'data.addComment.clientMutationId');

        return self::getJsonPathValue('data.addComment.node.id');
    }

    /**
     * testRemoveComment
     */
    public function testRemoveComment()
    {
        //disabled test temporarily
        self::assertTrue(true);

        return;

        $id = $this->testAddComment();

        self::mutation(
            'removeComment',
            [
                'input' => [
                    'id' => $id,
                    'clientMutationId' => (string) $clientMutationId = mt_rand(),
                ],
            ],
            [
                'id',
                'clientMutationId',
            ]
        );

        self::assertResponseCodeIsOK();
        self::assertJsonPathEquals($id, 'data.removeComment.id');
        self::assertJsonPathEquals($clientMutationId, 'data.removeComment.clientMutationId');
    }
}
