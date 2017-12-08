<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Mutation;

use Ynlo\GraphQLBundle\Model\NodeInterface;

/**
 * Class AddNodeMutation
 */
class AddNodeMutation extends AbstractMutationResolver
{
    /**
     * @param mixed $data
     */
    protected function process($data)
    {
        $this->prePersist($data);
        $this->getManager()->persist($data);
        $this->getManager()->flush();
        $this->postPersist($data);
    }

    /**
     * @param NodeInterface $node
     */
    protected function prePersist(NodeInterface $node)
    {
        //override
    }

    /**
     * @param NodeInterface $node
     */
    protected function postPersist(NodeInterface $node)
    {
        //override
    }
}
