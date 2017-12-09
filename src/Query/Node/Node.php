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

use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Definition\ArgumentDefinition;
use Ynlo\GraphQLBundle\Model\ID;
use Ynlo\GraphQLBundle\Resolver\AbstractResolver;

/**
 * @GraphQL\Query(name="node")
 * @GraphQL\Argument(name="id", type="ID!")
 */
class Node extends AbstractResolver
{
    /**
     * @param mixed $id
     *
     * @return null|object
     */
    public function __invoke($id)
    {
        if ($id instanceof ID) {
            $type = $id->getNodeType();
            $searchValue = $id->getDatabaseId();
            $searchField = 'id';
        } else {
            //when use a different field to fetch,
            //@see QueryGet::fetchBy
            $searchValue = $id;

            $type = $this->getContext()->getDefinition()->getType();

            /** @var ArgumentDefinition $arg */
            $arg = array_values($this->getContext()->getDefinition()->getArguments())[0];

            $field = $this->getContext()->getDefinitionManager()->getType($type)->getField($arg->getName());
            $searchField = $field->getOriginName();
        }

        $entityClass = $this->getContext()->getDefinitionManager()->getClassForType($type);

        return $this->getManager()
                    ->getRepository($entityClass)
                    ->findOneBy([$searchField => $searchValue]);
    }
}
