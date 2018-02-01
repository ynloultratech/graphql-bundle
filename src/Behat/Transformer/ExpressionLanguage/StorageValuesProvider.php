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
use Ynlo\GraphQLBundle\Behat\Storage\Storage;

/**
 * Allow access to stored values in the temporal storage
 *
 * Given a previous step: `And grab "{response.user}" in "user"`
 *
 * @example "{user.username}" => "admin"
 * @example "{user.profile.name}" => "Administrator"
 */
class StorageValuesProvider implements ExpressionPreprocessorInterface
{
    /**
     * @var Storage
     */
    protected $storage;

    /**
     * StorageValuesProvider constructor.
     *
     * @param Storage $storage
     */
    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    public function setUp(ExpressionLanguage $el, string &$expression, array &$values)
    {
        foreach ($this->storage as $name => $value) {
            $values[$name] = $value;
        }
    }
}
