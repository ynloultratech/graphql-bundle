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

class QueryExecutionContext
{
    /**
     * @var string
     */
    protected $queryId;

    /**
     * QueryExecutionContext constructor.
     */
    public function __construct()
    {
        $this->queryId = md5(time().mt_rand().spl_object_hash($this));
    }

    /**
     * @return string
     */
    public function getQueryId(): string
    {
        return $this->queryId;
    }
}
