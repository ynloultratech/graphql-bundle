<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Extension;

use Doctrine\ORM\QueryBuilder;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Resolver\ResolverContext;
use Ynlo\GraphQLBundle\Validator\ConstraintViolationList;

/**
 * Base extension for all GraphQL extensions
 */
abstract class AbstractGraphQLExtension implements GraphQLExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function configureDefinition(DefinitionInterface $definition, \ReflectionClass $refClass, Endpoint $endpoint)
    {
        // TODO: Implement configureDefinition() method.
    }

    /**
     * {@inheritdoc}
     */
    public function configureQuery(QueryBuilder $queryBuilder, $resolver, ResolverContext $context)
    {
        // TODO: Implement configureQuery() method.
    }

    /**
     * {@inheritdoc}
     */
    public function preValidate(&$data, $resolver, ResolverContext $context)
    {
        // TODO: Implement preValidate() method.
    }

    /**
     * {@inheritdoc}
     */
    public function postValidation($data, ConstraintViolationList $violations, $resolver, ResolverContext $context)
    {
        // TODO: Implement postValidation() method.
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(NodeInterface $node, $resolver, ResolverContext $context)
    {
        // TODO: Implement prePersist() method.
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist(NodeInterface $node, $resolver, ResolverContext $context)
    {
        // TODO: Implement postPersist() method.
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(NodeInterface $node, $resolver, ResolverContext $context)
    {
        // TODO: Implement preUpdate() method.
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate(NodeInterface $node, $resolver, ResolverContext $context)
    {
        // TODO: Implement postUpdate() method.
    }

    /**
     * {@inheritdoc}
     */
    public function preDelete(NodeInterface $node, $resolver, ResolverContext $context)
    {
        // TODO: Implement preDelete() method.
    }

    /**
     * {@inheritdoc}
     */
    public function postDelete(NodeInterface $node, $resolver, ResolverContext $context)
    {
        // TODO: Implement postDelete() method.
    }
}