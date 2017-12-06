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

use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Model\UpdateNodePayload;

/**
 * Class AddNode
 */
class AddNode extends AbstractNodeAction
{
    /**
     * {@inheritdoc}
     */
    public function __invoke($node, $dryRun = false, $clientMutationId = null): UpdateNodePayload
    {
        $violations = $this->validate($node);

        if ($violations || $dryRun) {
            $node = null;
        } else {
            $this->prePersist($node);
            $this->getManager()->persist($node);
            $this->getManager()->flush();
            $this->postPersist($node);
        }

        return new UpdateNodePayload($node, $violations, $clientMutationId);
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
