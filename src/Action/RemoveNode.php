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

use Ynlo\GraphQLBundle\Error\NodeNotFoundException;
use Ynlo\GraphQLBundle\Model\ID;
use Ynlo\GraphQLBundle\Model\NodeInterface;

/**
 * Class RemoveNode
 */
class RemoveNode extends AbstractNodeAction
{
    /**
     * @param ID                 $id
     * @param NodeInterface|null $node
     * @param string             $clientMutationId
     *
     * @return mixed
     */
    public function __invoke(ID $id, ?NodeInterface $node, $clientMutationId = null)
    {
        if (!$node) {
            throw new NodeNotFoundException();
        }

        $this->preRemove($node);
        $this->getManager()->remove($node);
        $this->getManager()->flush();
        $this->postRemove($node);

        return ['id' => $id, 'clientMutationId' => $clientMutationId];
    }

    /**
     * @param NodeInterface $node
     */
    protected function preRemove(NodeInterface $node)
    {
        //override
    }

    /**
     * @param NodeInterface $node
     */
    protected function postRemove(NodeInterface $node)
    {
        //override
    }
}
