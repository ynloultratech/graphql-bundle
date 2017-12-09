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

use Ynlo\GraphQLBundle\Model\AddNodePayload;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Validator\ConstraintViolationList;

/**
 * Class AddNodeMutation
 */
class AddNodeMutation extends AbstractMutationResolver
{
    /**
     * {@inheritdoc}
     */
    protected function process(&$data)
    {
        $this->prePersist($data);
        $this->getManager()->persist($data);
        $this->getManager()->flush();
        $this->postPersist($data);
    }

    /**
     * {@inheritdoc}
     */
    protected function returnPayload($data, ConstraintViolationList $violations, $inputSource)
    {
        if ($violations->count()) {
            $data = null;
        }

        return new AddNodePayload($data, $violations->all(), $inputSource['clientMutationId'] ?? null);
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
