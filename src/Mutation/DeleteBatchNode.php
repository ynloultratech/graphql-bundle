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

use GraphQL\Error\UserError;
use Symfony\Component\Form\FormEvent;
use Ynlo\GraphQLBundle\Error\NodeNotFoundException;
use Ynlo\GraphQLBundle\Model\DeleteBatchNodePayload;
use Ynlo\GraphQLBundle\Model\ID;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Validator\ConstraintViolationList;

/**
 * Class DeleteBatchNodeMutation
 */
class DeleteBatchNode extends AbstractMutationResolver
{
    /**
     * {@inheritdoc}
     */
    public function process(&$data)
    {
        foreach ($data['ids'] as $item) {
            $this->preDelete($item);
            foreach ($this->extensions as $extension) {
                $extension->preDelete($item, $this, $this->context);
            }

            $this->getManager()->remove($item);
        }

        $this->getManager()->flush();

        foreach ($data['ids'] as $item) {
            $this->postDelete($item);
            foreach ($this->extensions as $extension) {
                $extension->postDelete($item, $this, $this->context);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function returnPayload($data, ConstraintViolationList $violations, $inputSource)
    {
        $ids = [];
        foreach ($inputSource['ids'] as $id) {
            $ids[] = ID::createFromString($id);
        }

        $class = $this->getPayloadClass();

        return new $class($ids, $inputSource['clientMutationId'] ?? null);
    }

    /**
     * {@inheritDoc}
     */
    public function onSubmit(FormEvent $event)
    {
        $node = $this->context->getDefinition()->getNode();
        $class = $this->context->getEndpoint()->getClassForType($node);

        if ($event->getForm()->get('ids') && is_array($event->getForm()->get('ids')->getData())) {
            foreach ($event->getForm()->get('ids')->getData() as $node) {
                if (!$node instanceof NodeInterface || !$node->getId() || !is_a($node, $class)) {
                    throw new NodeNotFoundException();
                }
            }
        } else {
            throw new UserError('Batch error, invalid data');
        }
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
