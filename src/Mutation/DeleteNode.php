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
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Validator\ConstraintViolationList;

/**
 * Class DeleteNodeMutation
 */
class DeleteNode extends AbstractMutationResolver
{
    /**
     * @var NodeInterface
     */
    protected $deletedNode;

    /**
     * {@inheritdoc}
     */
    public function process(&$data)
    {
        $this->preDelete($data);
        foreach ($this->extensions as $extension) {
            $extension->preDelete($data, $this, $this->context);
        }

        $this->deletedNode = clone $data; //clone required to avoid node without id after delete

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
        $class = $this->getPayloadClass();

        return new $class(
            $this->deletedNode,
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
