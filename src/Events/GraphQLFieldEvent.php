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
use Ynlo\GraphQLBundle\Resolver\ResolverContext;

class GraphQLFieldEvent extends Event
{
    /**
     * @var ResolverContext
     */
    protected $context;

    /**
     * @var mixed|null
     */
    protected $value;

    /**
     * GraphQLFieldEvent constructor.
     *
     * @param ResolverContext $context
     * @param mixed|null      $value
     */
    public function __construct(ResolverContext $context, $value = null)
    {
        $this->context = $context;
        $this->value = $value;
    }

    /**
     * @return ResolverContext
     */
    public function getContext(): ResolverContext
    {
        return $this->context;
    }

    /**
     * @return mixed|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }
}
