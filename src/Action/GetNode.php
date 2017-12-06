<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Action;

use Ynlo\GraphQLBundle\Annotation as API;
use Ynlo\GraphQLBundle\Definition\ArgumentDefinition;
use Ynlo\GraphQLBundle\Model\ID;

/**
 * @API\Query(name="node", type="Node", args={
 *     @API\Arg(name="id", type="ID!")
 * })
 */
class GetNode extends AbstractNodeAction
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
            //@see GetNode::fetchBy
            $searchValue = $id;

            $type = $this->getContext()->getDefinition()->getReturnType();

            /** @var ArgumentDefinition $arg */
            $arg = array_values($this->getContext()->getDefinition()->getArgs())[0];

            $field = $this->getContext()->getDefinitionManager()->getType($type)->getField($arg->getName());
            $searchField = $field->getOriginName();
        }

        $entityClass = $this->getContext()->getDefinitionManager()->getType($type)->getClass();

        return $this->getManager()
                    ->getRepository($entityClass)
                    ->findOneBy([$searchField => $searchValue]);
    }
}
