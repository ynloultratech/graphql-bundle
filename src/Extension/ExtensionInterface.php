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
use Elastica\Query;
use Symfony\Component\Form\FormEvent;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Resolver\ResolverContext;

/**
 * ExtensionInterface
 */
interface ExtensionInterface
{
    /**
     * Configure the query builder to filter records or add a complex logic
     *
     * @param QueryBuilder|Query $queryBuilder
     * @param mixed              $resolver
     * @param ResolverContext    $context
     */
    public function configureQuery($queryBuilder, $resolver, ResolverContext $context);

    /**
     * @see http://api.symfony.com/4.0/Symfony/Component/Form/FormEvents.html
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event);

    /**
     * @see http://api.symfony.com/4.0/Symfony/Component/Form/FormEvents.html
     *
     * @param FormEvent $event
     */
    public function postSetData(FormEvent $event);

    /**
     * @see http://api.symfony.com/4.0/Symfony/Component/Form/FormEvents.html
     *
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event);

    /**
     * @see http://api.symfony.com/4.0/Symfony/Component/Form/FormEvents.html
     *
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event);

    /**
     * @see http://api.symfony.com/4.0/Symfony/Component/Form/FormEvents.html
     *
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event);

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
