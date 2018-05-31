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

use Faker\Factory;
use Ynlo\GraphQLBundle\Demo\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Demo\AppBundle\Entity\PostComment;

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
        $faker = Factory::create();

        /** @var Post $post */
        $post = self::getFixtureReference('post1');

        $mutation = <<<'GraphQL'
mutation($input: AddCommentInput!){
    comments {
        add (input: $input) {
            node {
                id
                body
                commentable {
                    ... on Post {
                        title
                    }
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
                    'commentable' => self::encodeID($post),
                    'body' => $comment = $faker->sentence,
                    'clientMutationId' => (string) $clientMutationId = mt_rand(),
                ],
            ]
        );

        self::assertRepositoryContains(PostComment::class, ['body' => $comment, 'post' => $post]);
        self::assertResponseJsonValueEquals($comment, 'data.comments.add.node.body');
        self::assertResponseJsonValueEquals($post->getTitle(), 'data.comments.add.node.commentable.title');
        self::assertResponseJsonValueEquals($clientMutationId, 'data.comments.add.clientMutationId');

        return self::getResponseJsonPathValue('data.comments.add.node.id');
    }

    /**
     * testRemoveComment
     */
    public function testDeleteComment()
    {
        $id = $this->testAddComment();

        $mutation = <<<'GraphQL'
mutation($input: DeleteCommentInput!){
    comments {
        delete (input: $input) {
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
                    'id' => $id,
                    'clientMutationId' => (string) $clientMutationId = mt_rand(),
                ],
            ]
        );

        self::assertResponseCodeIsOK();
        self::assertResponseJsonValueEquals($id, 'data.comments.delete.id');
        self::assertResponseJsonValueEquals($clientMutationId, 'data.comments.delete.clientMutationId');
    }
}
