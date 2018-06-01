<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Security\Authorization;

use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;

class AccessControlChecker
{
    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authChecker;

    /**
     * AccessControlChecker constructor.
     *
     * @param AuthorizationCheckerInterface $authChecker
     */
    public function __construct(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }

    /**
     * @param DefinitionInterface $definition
     * @param mixed|null          $subject
     *
     * @return bool
     */
    public function isGranted(DefinitionInterface $definition, $subject = null): bool
    {
        if ($this->isControlled($definition)) {
            return $this->authChecker->isGranted($this->getExpression($definition), $subject);
        }

        return true;
    }

    /**
     * @param DefinitionInterface $definition
     *
     * @return null|string
     */
    public function getMessage(DefinitionInterface $definition): ?string
    {
        return $definition->getMeta('access_control')['message'] ?? null;
    }

    /**
     * @param DefinitionInterface $definition
     *
     * @return null|Expression
     */
    public function getExpression(DefinitionInterface $definition): ?Expression
    {
        $accessControl = $definition->getMeta('access_control', []);
        if ($expressionSerialized = $accessControl['expression_serialized'] ?? null) {
            $expression = unserialize($expressionSerialized, ['allowed_classes' => true]);
        } else {
            $rawExpression = $accessControl['expression'] ?? null;
            $expression = new Expression($rawExpression);
        }

        return $expression;
    }

    /**
     * @param DefinitionInterface $definition
     *
     * @return bool
     */
    public function isControlled(DefinitionInterface $definition): bool
    {
        return (bool) ($definition->getMeta('access_control', [])['expression'] ?? false);
    }
}
