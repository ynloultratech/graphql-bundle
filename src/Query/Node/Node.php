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
use Ynlo\GraphQLBundle\Exception\Controlled\NotFoundError;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Resolver\AbstractResolver;

/**
 * @GraphQL\Query(name="node")
 * @GraphQL\Argument(name="id", type="ID!", internalName="node")
 */
class Node extends AbstractResolver
{
    protected $fetchBy = 'id';

    /**
     * @param mixed $node
     *
     * @return null|object
     */
    public function __invoke($node)
    {
        if (!$node) {
            throw new NotFoundError();
        }

        if ($node instanceof NodeInterface) {
            return $node;
        }

        //when use a different field to fetch,
        //@see QueryGet::fetchBy
        $searchValue = $node;

        $type = $this->getContext()->getNodeDefinition()->getName();

        $entityClass = $this->getContext()->getEndpoint()->getClassForType($type);

        return $this->getManager()
                    ->getRepository($entityClass)
                    ->findOneBy([$this->fetchBy => $searchValue]);
    }
}
