<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Model;

use Ynlo\GraphQLBundle\Annotation as GraphQL;

/**
 * @GraphQL\ObjectType()
 */
class PageInfo
{
    /**
     * @var string
     *
     * @GraphQL\Field(type="string")
     */
    protected $startCursor = null;

    /**
     * @var string
     *
     * @GraphQL\Field(type="string")
     */
    protected $endCursor = null;

    /**
     * @var bool
     *
     * @GraphQL\Field(type="bool!")
     */
    protected $hasNextPage = false;

    /**
     * @var bool
     *
     * @GraphQL\Field(type="bool!")
     */
    protected $hasPreviousPage = false;

    /**
     * @return string
     */
    public function getStartCursor(): ?string
    {
        return $this->startCursor;
    }

    /**
     * @param string $startCursor
     *
     * @return PageInfo
     */
    public function setStartCursor(string $startCursor): PageInfo
    {
        $this->startCursor = $startCursor;

        return $this;
    }

    /**
     * @return string
     */
    public function getEndCursor(): ?string
    {
        return $this->endCursor;
    }

    /**
     * @param string $endCursor
     *
     * @return PageInfo
     */
    public function setEndCursor(string $endCursor): PageInfo
    {
        $this->endCursor = $endCursor;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHasNextPage(): bool
    {
        return $this->hasNextPage;
    }

    /**
     * @param bool $hasNextPage
     *
     * @return PageInfo
     */
    public function setHasNextPage(bool $hasNextPage): PageInfo
    {
        $this->hasNextPage = $hasNextPage;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHasPreviousPage(): bool
    {
        return $this->hasPreviousPage;
    }

    /**
     * @param bool $hasPreviousPage
     *
     * @return PageInfo
     */
    public function setHasPreviousPage(bool $hasPreviousPage): PageInfo
    {
        $this->hasPreviousPage = $hasPreviousPage;

        return $this;
    }
}
