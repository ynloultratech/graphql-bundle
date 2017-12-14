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
use Ynlo\GraphQLBundle\Model\ID;
use Ynlo\GraphQLBundle\Resolver\AbstractResolver;

/**
 * @GraphQL\Query(name="node")
 * @GraphQL\Argument(name="id", type="ID!")
 */
class Node extends AbstractResolver
{
    protected $fetchBy = 'id';

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
        } else {
            //when use a different field to fetch,
            //@see QueryGet::fetchBy
            $searchValue = $id;

            $type = $this->getContext()->getNodeDefinition()->getName();
        }

        $entityClass = $this->getContext()->getEndpoint()->getClassForType($type);

        return $this->getManager()
                    ->getRepository($entityClass)
                    ->findOneBy([$this->fetchBy => $searchValue]);
    }
}
