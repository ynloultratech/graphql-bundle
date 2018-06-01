<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Plugin;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\ExpressionLanguage\ParsedExpression;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguage;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

/**
 * Compiles the expression used in AccessControl annotation to check later in AccessControlListener
 */
class AccessControlDefinitionPlugin extends AbstractDefinitionPlugin
{
    /**
     * {@inheritDoc}
     */
    public function buildConfig(ArrayNodeDefinition $root): void
    {
        $config = $root
            ->info('Control the access to fields and objects')
            ->children();

        $config->scalarNode('expression');
        $config->scalarNode('message');
    }

    /**
     * {@inheritDoc}
     */
    public function configure(DefinitionInterface $definition, Endpoint $endpoint, array $config): void
    {
        if ($config && $expression = $config['expression']) {
            $nodes =
                (new ExpressionLanguage())
                    ->parse($expression, ['token', 'user', 'object', 'roles', 'request', 'trust_resolver'])
                    ->getNodes();

            $config['expression_serialized'] = serialize(new ParsedExpression($expression, $nodes));
            $definition->setMeta($this->getName(), $config);
        }
    }
}
