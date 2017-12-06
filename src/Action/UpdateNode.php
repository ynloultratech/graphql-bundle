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
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Model\UpdateNodePayload;

/**
 * Class UpdateNode
 */
class UpdateNode extends AbstractNodeAction
{
    /**
     * @param NodeInterface|null $node
     * @param boolean            $dryRun
     * @param string             $clientMutationId
     *
     * @return mixed
     */
    public function __invoke($node, $dryRun = false, $clientMutationId = null): UpdateNodePayload
    {
        if (!$node || !$this->getManager()->contains($node)) {
            throw new NodeNotFoundException();
        }
        $violations = $this->validate($node);

        if ($violations || $dryRun) {
            $node = null;
        } else {
            $this->preUpdate($node);
            $this->getManager()->flush();
            $this->postUpdate($node);
        }

        return new UpdateNodePayload($node, $violations, $clientMutationId);
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
