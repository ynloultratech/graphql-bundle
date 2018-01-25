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
        self::send($query, ['id' => $id = self::getFixtureGlobalId('post1')]);

        self::assertResponseCodeIsOK();

        /** @var Post $post */
        $post = self::getFixtureReference('post1');
        self::assertResponseJsonValueEquals($id, 'data.node.id');
        self::assertResponseJsonValueEquals($post->getTitle(), 'data.node.title');
        self::assertResponseJsonValueEquals($post->getBody(), 'data.node.body');
    }

    /**
     * testGetNodes
     */
    public function testGetNodes()
    {
        /** @var Post $post1 */
        $post1 = self::getFixtureReference('post1');
        $id1 = self::encodeID($post1);

        /** @var Post $post1 */
        $post2 = self::getFixtureReference('post2');
        $id2 = self::encodeID($post2);

        /** @var Post $post3 */
        $post3 = self::getFixtureReference('post3');
        $id3 = self::encodeID($post3);

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
        self::assertResponseJsonValueEquals($id1, 'data.nodes[0].id');
        self::assertResponseJsonValueEquals($post1->getTitle(), 'data.nodes[0].title');
        self::assertResponseJsonValueEquals($post1->getBody(), 'data.nodes[0].body');

        self::assertResponseJsonValueEquals($id3, 'data.nodes[1].id');
        self::assertResponseJsonValueEquals($post3->getTitle(), 'data.nodes[1].title');
        self::assertResponseJsonValueEquals($post3->getBody(), 'data.nodes[1].body');

        self::assertResponseJsonValueEquals($id2, 'data.nodes[2].id');
        self::assertResponseJsonValueEquals($post2->getTitle(), 'data.nodes[2].title');
        self::assertResponseJsonValueEquals($post2->getBody(), 'data.nodes[2].body');
    }
}
