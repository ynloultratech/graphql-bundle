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

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Ynlo\GraphQLBundle\Definition\MutationDefinition;
use Ynlo\GraphQLBundle\Definition\ResolverContext;
use Ynlo\GraphQLBundle\Model\ConstraintViolation;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Validator\ValidatorBridge;

/**
 * Class AbstractNodeAction
 */
abstract class AbstractNodeAction implements APIActionInterface
{
    use ContainerAwareTrait;

    /**
     * @var ResolverContext
     */
    protected $context;

    /**
     * @return EntityManager
     */
    public function getManager(): EntityManager
    {
        return $this->container->get('doctrine')->getManager();
    }

    /**
     * @return ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->container->get('validator');
    }

    /**
     * @return ResolverContext
     */
    public function getContext(): ResolverContext
    {
        return $this->context;
    }

    /**
     * @param ResolverContext $context
     */
    public function setContext(ResolverContext $context)
    {
        $this->context = $context;
    }

    /**
     * @param NodeInterface $node
     *
     * @return ConstraintViolation[]
     */
    protected function validate(NodeInterface $node): array
    {
        $groups = [];
        $actionDefinition = $this->getContext()->getDefinition();
        if ($actionDefinition instanceof MutationDefinition) {
            $groups = $actionDefinition->getValidationGroups();
        }
        $this->preValidate($node);

        $violations = $this->getValidator()->validate($node, null, $groups);

        $this->postValidation($node, $violations);

        $definition = $this->getContext()->getDefinitionManager()->getType($actionDefinition->getNodeType());
        $validatorBridge = new ValidatorBridge($this->getContext()->getDefinitionManager());

        return $validatorBridge->convertViolations($violations, $definition);
    }

    /**
     * @param NodeInterface                    $node
     * @param ConstraintViolationListInterface $violations
     */
    protected function postValidation(NodeInterface $node, ConstraintViolationListInterface $violations)
    {
        //override in child
    }

    /**
     * @param NodeInterface $node
     */
    protected function preValidate(NodeInterface $node)
    {
        //override in child
    }
}
