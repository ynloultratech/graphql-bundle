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
 * Class UpdateNodeMutation
 */
class UpdateNodeMutation extends AbstractMutationResolver
{
    /**
     * @param mixed $data
     */
    protected function process($data)
    {
        $this->preUpdate($data);
        $this->getManager()->flush();
        $this->postUpdate($data);
    }

    /**
     * @param NodeInterface $node
     */
    protected function preUpdate(NodeInterface $node)
    {
        //override
    }

    /**
     * @param NodeInterface $node
     */
    protected function postUpdate(NodeInterface $node)
    {
        //override
    }
}
