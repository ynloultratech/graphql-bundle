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
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Model\UpdateNodePayload;
use Ynlo\GraphQLBundle\Validator\ConstraintViolationList;

/**
 * Class UpdateNodeMutation
 */
class UpdateNodeMutation extends AbstractMutationResolver
{
    /**
     * {@inheritdoc}
     */
    protected function process(&$data)
    {
        $this->preUpdate($data);
        $this->getManager()->flush();
        $this->postUpdate($data);
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
     * {@inheritdoc}
     */
    protected function returnPayload($data, ConstraintViolationList $violations, $inputSource)
    {
        if ($violations->count()) {
            $data = null;
        }

        return new UpdateNodePayload($data, $violations->all(), $inputSource['clientMutationId'] ?? null);
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
