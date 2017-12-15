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
use Ynlo\GraphQLBundle\Extension\ExtensionManager;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Model\UpdateNodePayload;
use Ynlo\GraphQLBundle\Validator\ConstraintViolationList;

/**
 * Class UpdateNodeMutation
 */
class UpdateNode extends AbstractMutationResolver
{
    /**
     * {@inheritdoc}
     */
    public function process(&$data)
    {
        $this->preUpdate($data);
        foreach ($this->extensions as $extension) {
            $extension->preUpdate($data, $this, $this->context);
        }

        $this->getManager()->flush();

        $this->postUpdate($data);
        foreach ($this->extensions as $extension) {
            $extension->postUpdate($data, $this, $this->context);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onSubmit(FormEvent $event)
    {
        if (!$event->getData() instanceof NodeInterface || !$event->getData()->getId()) {
            throw new NodeNotFoundException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function returnPayload($data, ConstraintViolationList $violations, $inputSource)
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
