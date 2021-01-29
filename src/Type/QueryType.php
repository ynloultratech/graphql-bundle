<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\Events\GraphQLEvents;
use Ynlo\GraphQLBundle\Events\GraphQLFieldEvent;
use Ynlo\GraphQLBundle\Resolver\ContextBuilder;
use Ynlo\GraphQLBundle\Resolver\ResolverContext;
use Ynlo\GraphQLBundle\Resolver\ResolverExecutor;
use Ynlo\GraphQLBundle\Type\Definition\EndpointAwareInterface;
use Ynlo\GraphQLBundle\Type\Definition\EndpointAwareTrait;
use Ynlo\GraphQLBundle\Type\Registry\TypeRegistry;
use Ynlo\GraphQLBundle\Util\GraphQLBuilder;

/**
 * Class QueryType
 */
class QueryType extends ObjectType implements
    ContainerAwareInterface,
    EndpointAwareInterface
{
    use ContainerAwareTrait;
    use EndpointAwareTrait;

    /**
     * QueryType constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $defaults = [
            'name' => 'Query',
            'fields' => function () {
                $queries = [];
                foreach ($this->endpoint->allQueries() as $query) {
                    $queries[$query->getName()] = $this->getQueryConfig($query);
                }

                return $queries;
            },
        ];
        parent::__construct(array_merge($defaults, $config));
    }

    /**
     * @param QueryDefinition $query
     *
     * @return array
     */
    protected function getQueryConfig(QueryDefinition $query): array
    {
        $config['type'] = TypeRegistry::get($query->getType());
        if ($query->isList()) {
            $config['type'] = Type::listOf($config['type']);
        }

        $config['args'] = GraphQLBuilder::buildArguments($query);


        $container = $this->container;
        $config['resolve'] = function ($root, array $args, ResolverContext $context, ResolveInfo $resolveInfo) use ($container, $query) {
            $eventDispatcher = $this->container->get(EventDispatcherInterface::class);

            $context = ContextBuilder::create($this->endpoint)
                                     ->setRoot($root)
                                     ->setDefinition($query)
                                     ->setResolveInfo($resolveInfo)
                                     ->setMetas($context->getMetas())
                                     ->build();

            $event = new GraphQLFieldEvent($context);
            $eventDispatcher->dispatch($event, GraphQLEvents::PRE_READ_FIELD);

            $executor = new ResolverExecutor($container, $query);

            $value = $executor($root, $args, $context, $resolveInfo);
            $event->setValue($value);

            $eventDispatcher->dispatch($event, GraphQLEvents::POST_READ_FIELD);

            return $value;
        };
        $config['description'] = $query->getDescription();
        $config['deprecationReason'] = $query->getDeprecationReason();

        return $config;
    }
}
