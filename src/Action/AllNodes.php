<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Action;

use Doctrine\ORM\QueryBuilder;

/**
 * Class AllNodes
 */
class AllNodes extends AbstractNodeAction
{
    /**
     * @var string
     */
    protected $queryAlias = 'o';

    /**
     * @return mixed
     */
    public function __invoke()
    {
        $objectType = $this->context->getDefinition()->getReturnType();
        $entityClass = $this->context->getDefinitionManager()->getType($objectType)->getClass();

        $qb = $this->createQuery($entityClass);

        return $qb->getQuery()->execute();
    }

    /**
     * @param string $class
     *
     * @return QueryBuilder
     */
    protected function createQuery($class): QueryBuilder
    {
        return $this->getManager()
                    ->getRepository($class)
                    ->createQueryBuilder($this->queryAlias);
    }
}
