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
class FieldExpressionResolver extends AbstractResolver
{
    /**
     * {@inheritDoc}
     */
    public function __invoke($root)
    {
        $language = new ExpressionLanguage();
        if ($this->getContext()->getDefinition()->hasMeta('expression')) {
            $expression = $this->getContext()->getDefinition()->getMeta('expression');

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
