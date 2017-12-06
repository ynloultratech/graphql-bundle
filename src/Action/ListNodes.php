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

/**
 * Class ListNodes
 */
class ListNodes extends AbstractNodeAction
{
    /**
     * @return mixed
     */
    public function __invoke()
    {
        $objectType = $this->context->getDefinition()->getReturnType();
        $entityClass = $this->context->getDefinitionManager()->getType($objectType)->getClass();

        return $this->getManager()->getRepository($entityClass)->findAll();
    }
}
