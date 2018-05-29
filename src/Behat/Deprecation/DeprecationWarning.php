<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Deprecation;

/**
 * Storage a deprecation message with details
 */
class DeprecationWarning
{
    /**
     * @var string
     */
    protected $message;

    /**
     * @var string|null
     */
    protected $file;

    /**
     * @var int|null
     */
    protected $line;

    /**
     * DeprecationWarning constructor.
     *
     * @param string      $message
     * @param null|string $file
     * @param int|null    $line
     */
    public function __construct(string $message, ?string $file = null, ?int $line = null)
    {
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return null|string
     */
    public function getFile(): ?string
    {
        return $this->file;
    }

    /**
     * @return int|null
     */
    public function getLine(): ?int
    {
        return $this->line;
    }
}
