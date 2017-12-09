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

use Ynlo\GraphQLBundle\Error\NodeNotFoundException;
use Ynlo\GraphQLBundle\Model\DeleteNodePayload;
use Ynlo\GraphQLBundle\Model\ID;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Validator\ConstraintViolationList;

/**
 * Class DeleteNodeMutation
 */
class DeleteNodeMutation extends AbstractMutationResolver
{
    /**
     * {@inheritdoc}
     */
    protected function process(&$data)
    {
        $this->preDelete($data);
        $this->getManager()->remove($data);
        $this->getManager()->flush();
        $this->postDelete($data);
    }

    /**
     * {@inheritdoc}
     */
    protected function returnPayload($data, ConstraintViolationList $violations, $inputSource)
    {
        return new DeleteNodePayload(
            $inputSource['id'] ? ID::createFromString($inputSource['id']) : null,
            $inputSource['clientMutationId'] ?? null
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function onSubmit($inputSource, &$normData)
    {
        if ($normData instanceof NodeInterface && $normData->getId()) {
            return;
        }

        throw new NodeNotFoundException();
    }

    /**
     * @param NodeInterface $node
     */
    protected function preDelete(NodeInterface $node)
    {
        //override
    }

    /**
     * @param NodeInterface $node
     */
    protected function postDelete(NodeInterface $node)
    {
        //override
    }
}
