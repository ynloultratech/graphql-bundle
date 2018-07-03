<?php

/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Loader\Annotation;

use Doctrine\Common\Util\Inflector;
use Ynlo\GraphQLBundle\Annotation;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Query\Node\AllNodesWithPagination;
use Ynlo\GraphQLBundle\Util\ClassUtils;

class QueryListAnnotationParser extends QueryAnnotationParser
{
    /**
     * {@inheritdoc}
     */
    public function supports($annotation): bool
    {
        return $annotation instanceof Annotation\QueryList;
    }

    /**
     * {@inheritdoc}
     *
     * @param Annotation\QueryList $annotation
     */
    public function parse($annotation, \ReflectionClass $refClass, Endpoint $endpoint)
    {
        if (!$endpoint->hasTypeForClass($refClass->getName())) {
            throw new \RuntimeException(sprintf('Can\'t apply list operations to "%s", CRUD operations can only be applied to valid GraphQL object types.', $refClass->getName()));
        }

        if (!$refClass->implementsInterface(NodeInterface::class)) {
            throw new \RuntimeException(sprintf('Can\'t apply list operations to "%s", CRUD operations can only be applied to nodes. You are implementing "%s" in this class?', $refClass->getName(), NodeInterface::class));
        }

        $definition = $endpoint->getType($endpoint->getTypeForClass($refClass->getName()));

        $annotation->name = $annotation->name ?? 'all'.Inflector::pluralize(ucfirst($definition->getName()));
        $annotation->type = sprintf('[%s]', $annotation->type ?? $definition->getName());
        $annotation->options = array_merge(['pagination' => true], $annotation->options);

        if ($annotation->limit) {
            if (!\is_array($annotation->options['pagination'])) {
                $annotation->options['pagination'] = [];
            }
            $annotation->options['pagination']['limit'] = $annotation->limit;
        }

        if ($annotation->filters) {
            if (!\is_array($annotation->options['pagination'])) {
                $annotation->options['pagination'] = [];
            }
            $annotation->options['pagination']['filters'] = $annotation->filters;
        }

        $bundleNamespace = ClassUtils::relatedBundleNamespace($refClass->getName());

        $resolver = ClassUtils::applyNamingConvention($bundleNamespace, 'Query', $definition->getName(), $annotation->name);
        if (class_exists($resolver)) {
            $annotation->resolver = $resolver;
        }

        $resolverReflection = new \ReflectionClass(AllNodesWithPagination::class);

        parent::parse($annotation, $resolverReflection, $endpoint);
    }
}
