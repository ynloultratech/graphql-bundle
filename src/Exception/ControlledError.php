<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Exception;

use GraphQL\Error\UserError;

abstract class ControlledError extends UserError implements ControlledErrorInterface
{
    /**
     * @var string
     */
    protected $description;

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Throw a controlled error dynamically
     *
     * @param int|string  $code
     * @param string      $message
     * @param string|null $category
     *
     * @return ControlledError
     */
    public static function create($code, $message = null, $category = null)
    {
        return new class($message, $code, $category) extends ControlledError
        {
            /**
             * @var string
             */
            protected $category;

            /**
             * @param string $message
             * @param string $code
             * @param null   $category
             */
            public function __construct($message, $code, $category = null)
            {
                if ($category) {
                    $this->category = $category;
                }
                parent:: __construct($message, $code);
            }

            /**
             * @return string
             */
            public function getCategory(): string
            {
                return $this->category ?? parent::getCategory();
            }
        };
    }
}
