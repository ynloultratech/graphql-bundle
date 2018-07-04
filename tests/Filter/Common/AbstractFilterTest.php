<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Filter\Common;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Filter\FilterContext;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;

abstract class AbstractFilterTest extends MockeryTestCase
{
    /**
     * @var FilterContext
     */
    protected $context;

    /**
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * @var EntityManagerInterface|Mock
     */
    protected $em;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $endpoint = new Endpoint('default');
        $node = new ObjectDefinition();
        $node->setClass(Post::class);
        $field = new FieldDefinition();
        $field->setName('fieldName');
        $field->setOriginName('fieldName');
        $this->context = new FilterContext($endpoint, $node, $field);

        $this->em = \Mockery::mock(EntityManagerInterface::class);
        $this->em->allows('getExpressionBuilder')->andReturn(new Expr());

        $this->qb = new QueryBuilder($this->em);
        $this->qb->select('p')
                 ->from('Post', 'p');
    }
}
