<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Resolver;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * FieldExpressionResolver
 */
class FieldExpressionResolver
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ResolverContext $context, $root)
    {
        $language = new ExpressionLanguage();
        if ($context->getDefinition()->hasMeta('expression')) {
            $expression = $context->getDefinition()->getMeta('expression');

            return $language->evaluate(
                $expression,
                [
                    'this' => $root,
                ]
            );
        }

        return null;
    }
}
