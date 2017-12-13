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
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Resolver\ResolverContext;
use Ynlo\GraphQLBundle\Validator\ConstraintViolationList;

/**
 * GraphQLExtensionInterface
 */
interface GraphQLExtensionInterface
{
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
