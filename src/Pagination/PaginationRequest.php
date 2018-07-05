<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Pagination;

/**
 * PaginationRequest
 */
class PaginationRequest
{
    /**
     * @var int
     */
    protected $first;

    /**
     * @var int
     */
    protected $last;

    /**
     * @var string
     */
    protected $after;

    /**
     * @var string
     */
    protected $before;

    /**
     * PaginationRequest constructor.
     *
     * @param string|null $first
     * @param string|null $last
     * @param string|null $after
     * @param string|null $before
     */
    public function __construct($first = null, $last = null, $after = null, $before = null)
    {
        $this->first = $first;
        $this->last = $last;
        $this->after = $after;
        $this->before = $before;
    }

    /**
     * @return int
     */
    public function getFirst(): ?int
    {
        return $this->first;
    }

    /**
     * @param int $first
     *
     * @return PaginationRequest
     */
    public function setFirst(int $first): PaginationRequest
    {
        $this->first = $first;

        return $this;
    }

    /**
     * @return int
     */
    public function getLast(): ?int
    {
        return $this->last;
    }

    /**
     * @param int $last
     *
     * @return PaginationRequest
     */
    public function setLast(int $last): PaginationRequest
    {
        $this->last = $last;

        return $this;
    }

    /**
     * @return string
     */
    public function getAfter(): ?string
    {
        return $this->after;
    }

    /**
     * @param string $after
     *
     * @return PaginationRequest
     */
    public function setAfter(string $after): PaginationRequest
    {
        $this->after = $after;

        return $this;
    }

    /**
     * @return string
     */
    public function getBefore(): ?string
    {
        return $this->before;
    }

    /**
     * @param string $before
     *
     * @return PaginationRequest
     */
    public function setBefore(string $before): PaginationRequest
    {
        $this->before = $before;

        return $this;
    }
}
