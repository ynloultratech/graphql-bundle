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

use Doctrine\Common\Util\Inflector;
use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Definition\ArgumentDefinition;
use Ynlo\GraphQLBundle\Model\ID;
use Ynlo\GraphQLBundle\Resolver\AbstractResolver;

/**
 * @GraphQL\Query(list=true)
 * @GraphQL\Argument(name="ids", type="[ID!]!")
 */
class Nodes extends AbstractResolver
{
    /**
     * @param ID[]|mixed[] $ids
     *
     * @return mixed
     */
    public function __invoke($ids)
    {
        if (empty($ids)) {
            return [];
        }

        $types = [];
        $expectedResultsOrder = [];

        if (current($ids) instanceof ID) {
            $searchField = 'id';
            foreach ($ids as $id) {
                $types[$id->getNodeType()][] = $id->getDatabaseId();
                $expectedResultsOrder[md5($id->getNodeType().$id->getDatabaseId())] = null;
            }
        } else {
            //when use a different field to fetch,
            //@see QueryGet::fetchBy
            $type = $this->getContext()->getDefinition()->getType();
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

            $searchField = $field->getOriginName();
            $types[$type] = $ids;
            foreach ($ids as $identifier) {
                $expectedResultsOrder[md5($type.$identifier)] = null;
            }
        }

        foreach ($types as $type => $searchValues) {
            if ($this->getContext()->getEndpoint()->hasType($type)) {
                foreach ($searchValues as $searchValue) {
                    //TODO: improve this to find all nodes in the same Repo with only one query, NOTE: the order and empty results are very IMPORTANT!
                    //The list of given id should match with the list of results including non-found nodes
                    $entity = $this->getContext()->getEndpoint()->getClassForType($type);
                    $result = $this->getManager()->getRepository($entity)->findOneBy([$searchField => $searchValue]);
                    $expectedResultsOrder[md5($type.$searchValue)] = $result;
                }
            }
        }

        return array_values($expectedResultsOrder);
    }
}
