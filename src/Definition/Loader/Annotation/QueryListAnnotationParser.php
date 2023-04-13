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

use Ynlo\GraphQLBundle\Annotation;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Query\Node\AllNodesWithPagination;
use Ynlo\GraphQLBundle\Util\ClassUtils;
use Ynlo\GraphQLBundle\Util\Inflector;
use Ynlo\GraphQLBundle\Util\TypeUtil;

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
        $annotation->type = sprintf('[%s]', TypeUtil::normalize($annotation->type ?? $definition->getName()));

        $pagination = new Annotation\Plugin\Pagination();
        $pagination->enabled = true;
        $paginationDefined = false;
        foreach ($annotation->options as $option) {
            if ($option instanceof Annotation\Plugin\Pagination) {
                $paginationDefined = true;
                $pagination = $option;
            }
        }
        if (!$paginationDefined) {
            $annotation->options[] = $pagination;
        }

        if ($annotation->limit) {
            $pagination->limit = $annotation->limit;
        }

        if ($annotation->elastic) {
            $pagination->elastic = $annotation->elastic;
        }

        if ($annotation->filters) {
            $pagination->filters = $annotation->filters;
        }

        if ($annotation->orderBy) {
            $pagination->orderBy = $annotation->orderBy;
        }

        if ($annotation->searchFields) {
            $pagination->searchFields = $annotation->searchFields;
        }

        $bundleNamespace = ClassUtils::relatedBundleNamespace($refClass->getName());

        $resolver = ClassUtils::applyNamingConvention($bundleNamespace, 'Query', $definition->getName(), $annotation->name);
        if ($resolver && class_exists($resolver)) {
            $annotation->resolver = $resolver;
        }

        $resolverReflection = new \ReflectionClass(AllNodesWithPagination::class);

        parent::parse($annotation, $resolverReflection, $endpoint);
    }
}
