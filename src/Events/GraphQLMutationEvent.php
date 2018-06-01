<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormEvent;
use Ynlo\GraphQLBundle\Resolver\ResolverContext;

class GraphQLMutationEvent extends Event
{
    /**
     * @var ResolverContext
     */
    protected $context;

    /**
     * @var FormEvent
     */
    protected $formEvent;

    /**
     * @var mixed
     */
    protected $payload;

    /**
     * GraphQLMutationEvent constructor.
     *
     * @param ResolverContext $context
     * @param FormEvent       $formEvent
     */
    public function __construct(ResolverContext $context, FormEvent $formEvent)
    {
        $this->context = $context;
        $this->formEvent = $formEvent;
    }

    /**
     * @return ResolverContext
     */
    public function getContext(): ResolverContext
    {
        return $this->context;
    }

    /**
     * @return FormEvent
     */
    public function getFormEvent(): FormEvent
    {
        return $this->formEvent;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param mixed $payload
     *
     * @return GraphQLMutationEvent
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;

        return $this;
    }
}
