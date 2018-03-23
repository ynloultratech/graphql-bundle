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

use Symfony\Component\Form\FormEvent;
use Ynlo\GraphQLBundle\Error\NodeNotFoundException;
use Ynlo\GraphQLBundle\Model\DeleteNodePayload;
use Ynlo\GraphQLBundle\Model\ID;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Validator\ConstraintViolationList;

/**
 * Class DeleteNodeMutation
 */
class DeleteNode extends AbstractMutationResolver
{
    /**
     * {@inheritdoc}
     */
    public function process(&$data)
    {
        $this->preDelete($data);
        foreach ($this->extensions as $extension) {
            $extension->preDelete($data, $this, $this->context);
        }

        $this->getManager()->remove($data);
        $this->getManager()->flush();

        $this->postDelete($data);
        foreach ($this->extensions as $extension) {
            $extension->postDelete($data, $this, $this->context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function returnPayload($data, ConstraintViolationList $violations, $inputSource)
    {
        return new DeleteNodePayload(
            $inputSource['id'] ? ID::createFromString($inputSource['id']) : null,
            $inputSource['clientMutationId'] ?? null
        );
    }

    /**
     * {@inheritDoc}
     */
    public function onSubmit(FormEvent $event)
    {
        $node = $this->context->getDefinition()->getNode();
        $class = $this->context->getEndpoint()->getClassForType($node);

        if (!$event->getData() instanceof NodeInterface || !$event->getData()->getId() || !is_a($event->getData(), $class)) {
            throw new NodeNotFoundException();
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
