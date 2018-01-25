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
use Ynlo\GraphQLBundle\Test\ApiTestCase;

/**
 * Class NoteTest
 */
class NoteTest extends ApiTestCase
{
    /**
     * testGetNode
     */
    public function testGetNode()
    {
        /** @var Post $post */
        $post = self::getFixtureReference('post1');

        $query = <<<'GraphQL'
query($id: ID!){
    node(id: $id) {
        id
        ... on Post {
            title
            body
        }
    }
}
GraphQL;
        self::send($query, ['id' => $id = self::encodeID('Post', $post)]);

        self::assertResponseCodeIsOK();
        self::assertResponseJsonPathEquals($id, 'data.node.id');
        self::assertResponseJsonPathEquals($post->getTitle(), 'data.node.title');
        self::assertResponseJsonPathEquals($post->getBody(), 'data.node.body');
    }

    /**
     * testGetNodes
     */
    public function testGetNodes()
    {
        /** @var Post $post1 */
        $post1 = self::getFixtureReference('post1');
        $id1 = self::encodeID('Post', $post1);

        /** @var Post $post1 */
        $post2 = self::getFixtureReference('post2');
        $id2 = self::encodeID('Post', $post2);

        /** @var Post $post3 */
        $post3 = self::getFixtureReference('post3');
        $id3 = self::encodeID('Post', $post3);

        $query = <<<'GraphQL'
query($ids: [ID!]!){
    nodes(ids: $ids) {
        id
        ... on Post {
            title
            body
        }
    }
}
GraphQL;

        self::send($query, ['ids' => [$id1, $id3, $id2]]);

        self::assertResponseCodeIsOK();
        self::assertResponseJsonPathEquals($id1, 'data.nodes[0].id');
        self::assertResponseJsonPathEquals($post1->getTitle(), 'data.nodes[0].title');
        self::assertResponseJsonPathEquals($post1->getBody(), 'data.nodes[0].body');

        self::assertResponseJsonPathEquals($id3, 'data.nodes[1].id');
        self::assertResponseJsonPathEquals($post3->getTitle(), 'data.nodes[1].title');
        self::assertResponseJsonPathEquals($post3->getBody(), 'data.nodes[1].body');

        self::assertResponseJsonPathEquals($id2, 'data.nodes[2].id');
        self::assertResponseJsonPathEquals($post2->getTitle(), 'data.nodes[2].title');
        self::assertResponseJsonPathEquals($post2->getBody(), 'data.nodes[2].body');
    }
}
