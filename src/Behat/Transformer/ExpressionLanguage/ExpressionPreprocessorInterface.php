<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Transformer\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Implements this interface in a class capable of setup a expression to add values,
 * register functions or alter the expression before its evaluation
 */
interface ExpressionPreprocessorInterface
{
    public function setUp(ExpressionLanguage $el, string &$expression, array &$values);
}
