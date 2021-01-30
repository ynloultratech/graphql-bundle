<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Query\Node;

use Doctrine\ORM\EntityRepository;
use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Definition\ArgumentDefinition;
use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Resolver\AbstractResolver;
use Ynlo\GraphQLBundle\Util\Inflector;

/**
 * @GraphQL\Query(type="[]")
 * @GraphQL\Argument(name="ids", type="[ID!]!", internalName="nodes")
 */
class Nodes extends AbstractResolver
{
    protected $fetchBy = 'id';

    /**
     * @param mixed[] $nodes
     *
     * @return mixed
     */
    public function __invoke($nodes)
    {
        if (empty($nodes)) {
            return [];
        }

        $types = [];
        $expectedResultsOrder = [];

        if (current($nodes) instanceof NodeInterface) {
            return $nodes;
        }

        //when use a different field to fetch,
        //@see QueryGet::fetchBy
        $type = $this->getContext()->getDefinition()->getType();

        /** @var FieldsAwareDefinitionInterface $objectDefinition */
        $objectDefinition = $this->getContext()->getEndpoint()->getType($type);

        /** @var ArgumentDefinition $arg */
        $arg = array_values($this->getContext()->getDefinition()->getArguments())[0];

        $field = null;
        if ($objectDefinition->hasField($arg->getName())) {
            $field = $objectDefinition->getField($arg->getName());
        } elseif ($objectDefinition->hasField(Inflector::singularize($arg->getName()))) { //by convention, singularize
            $field = $objectDefinition->getField(Inflector::singularize($arg->getName()));
        }

        if (null === $field) {
            throw new \RuntimeException(sprintf('Can`t resolve the field `%s` inside type `%s`', $arg->getName(), $type));
        }

        $types[$type] = $nodes;
        foreach ($nodes as $identifier) {
            $expectedResultsOrder[md5($type.$identifier)] = null;
        }

        foreach ($types as $type => $searchValues) {
            if ($this->getContext()->getEndpoint()->hasType($type)) {
                $entity = $this->getContext()->getEndpoint()->getClassForType($type);

                /** @var EntityRepository $repo */
                $repo = $this->getManager()->getRepository($entity);

                $findBy = sprintf('o.%s', $this->fetchBy);

                $qb = $repo->createQueryBuilder('o', $findBy);
                $entities = $qb->where($qb->expr()->in($findBy, $searchValues))
                               ->getQuery()
                               ->getResult();

                foreach ($entities as $searchValue => $entity) {
                    $expectedResultsOrder[md5($type.$searchValue)] = $entity;
                }
            }
        }

        return array_values($expectedResultsOrder);
    }
}
