<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Definition\Plugin;

use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ParsedExpression;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguage;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\Plugin\AccessControlDefinitionPlugin;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

class AccessControlDefinitionPluginTest extends TestCase
{
    public function testConfigure()
    {
        $plugin = new AccessControlDefinitionPlugin();

        $definition = new FieldDefinition();
        $config = [
            'expression' => 'has_role("ADMIN")',
            'message' => 'Require admin role',
        ];
        $definition->setMeta('access_control', $config);

        $endpoint = new Endpoint('default');

        $nodes = (new ExpressionLanguage())
            ->parse($config['expression'], ['token', 'user', 'object', 'roles', 'request', 'trust_resolver'])
            ->getNodes();

        $config['expression_serialized'] = serialize(new ParsedExpression($config['expression'], $nodes));

        $plugin->configure($definition, $endpoint, $config);

        self::assertEquals($config['expression_serialized'], $definition->getMeta('access_control')['expression_serialized']);
    }
}
