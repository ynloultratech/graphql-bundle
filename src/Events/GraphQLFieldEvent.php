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
use Ynlo\GraphQLBundle\Resolver\FieldExecutionContext;

class GraphQLFieldEvent extends Event
{

    /**
     * @var GraphQLFieldInfo
     */
    protected $info;

    /**
     * @var mixed
     */
    protected $root;

    /**
     * @var array
     */
    protected $args = [];

    /**
     * @var FieldExecutionContext
     */
    protected $context;

    /**
     * @var mixed|null
     */
    protected $value;

    /**
     * GraphQLFieldEvent constructor.
     *
     * @param GraphQLFieldInfo      $info
     * @param mixed                 $root
     * @param array                 $args
     * @param FieldExecutionContext $context
     * @param mixed|null            $value
     */
    public function __construct(GraphQLFieldInfo $info, $root, array $args, FieldExecutionContext $context = null, $value = null)
    {
        $this->info = $info;
        $this->root = $root;
        $this->args = $args;
        $this->context = $context;
        $this->value = $value;
    }

    /**
     * @return GraphQLFieldInfo
     */
    public function getInfo(): GraphQLFieldInfo
    {
        return $this->info;
    }

    /**
     * @return mixed
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @return FieldExecutionContext
     */
    public function getContext()
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
