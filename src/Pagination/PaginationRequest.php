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
     * @var int
     */
    protected $page;

    /**
     * PaginationRequest constructor.
     *
     * @param string|null $first
     * @param string|null $last
     * @param string|null $after
     * @param string|null $before
     * @param string|null $page
     */
    public function __construct($first = null, $last = null, $after = null, $before = null, $page = null)
    {
        $this->first = abs($first);
        $this->last = abs($last);
        $this->after = $after;
        $this->before = $before;
        $this->page = abs($page);
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
        $this->first = abs($first);

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
        $this->last = abs($last);

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

    /**
     * @return int|null
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * @param int $page
     *
     * @return PaginationRequest
     */
    public function setPage(int $page): PaginationRequest
    {
        $this->page = abs($page);

        return $this;
    }
}
