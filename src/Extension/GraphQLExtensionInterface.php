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
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionManager;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Resolver\ResolverContext;
use Ynlo\GraphQLBundle\Validator\ConstraintViolationList;

/**
 * GraphQLExtensionInterface
 */
interface GraphQLExtensionInterface
{
    /**
     * Use this to override or add some custom parts to definitions
     *
     * @param DefinitionInterface $definition        definition to configure
     * @param \ReflectionClass    $refClass          Class where definition has been loaded
     * @param DefinitionManager   $definitionManager contains all definitions for current endpoint
     */
    public function configureDefinition(DefinitionInterface $definition, \ReflectionClass $refClass, DefinitionManager $definitionManager);

    /**
     * Configure the query builder to filter records or add a complex logic
     *
     * @param QueryBuilder    $queryBuilder
     * @param mixed           $resolver
     * @param ResolverContext $context
     */
    public function configureQuery(QueryBuilder $queryBuilder, $resolver, ResolverContext $context);

    /**
     * @param array           $data
     * @param mixed           $resolver
     * @param ResolverContext $context
     *
     * @return mixed
     */
    public function preValidate(&$data, $resolver, ResolverContext $context);

    /**
     * Can use this to add your custom validations errors
     *
     * @param mixed                   $data
     * @param ConstraintViolationList $violations
     * @param mixed                   $resolver
     * @param ResolverContext         $context
     */
    public function postValidation($data, ConstraintViolationList $violations, $resolver, ResolverContext $context);

    /**
     * @param NodeInterface   $node
     * @param mixed           $resolver
     * @param ResolverContext $context
     *
     * @return mixed
     */
    public function prePersist(NodeInterface $node, $resolver, ResolverContext $context);

    /**
     * @param NodeInterface   $node
     * @param mixed           $resolver
     * @param ResolverContext $context
     *
     * @return mixed
     */
    public function postPersist(NodeInterface $node, $resolver, ResolverContext $context);

    /**
     * @param NodeInterface   $node
     * @param mixed           $resolver
     * @param ResolverContext $context
     *
     * @return mixed
     */
    public function preUpdate(NodeInterface $node, $resolver, ResolverContext $context);

    /**
     * @param NodeInterface   $node
     * @param mixed           $resolver
     * @param ResolverContext $context
     *
     * @return mixed
     */
    public function postUpdate(NodeInterface $node, $resolver, ResolverContext $context);

    /**
     * @param NodeInterface   $node
     * @param mixed           $resolver
     * @param ResolverContext $context
     *
     * @return mixed
     */
    public function preDelete(NodeInterface $node, $resolver, ResolverContext $context);

    /**
     * @param NodeInterface   $node
     * @param mixed           $resolver
     * @param ResolverContext $context
     *
     * @return mixed
     */
    public function postDelete(NodeInterface $node, $resolver, ResolverContext $context);
}
