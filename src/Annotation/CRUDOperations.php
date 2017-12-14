<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Annotation;

/**
 * @Annotation()
 */
class CRUDOperations
{
    /**
     * @var \Ynlo\GraphQLBundle\Annotation\Query
     */
    public $get;

    /**
     * @var \Ynlo\GraphQLBundle\Annotation\Query
     */
    public $gets;

    /**
     * @var \Ynlo\GraphQLBundle\Annotation\Query
     */
    public $all;

    /**
     * @var \Ynlo\GraphQLBundle\Annotation\Mutation
     */
    public $add;

    /**
     * @var \Ynlo\GraphQLBundle\Annotation\Mutation
     */
    public $update;

    /**
     * @var \Ynlo\GraphQLBundle\Annotation\Mutation
     */
    public $delete;

    /**
     * Operations to include
     *
     * @var array
     */
    public $include = ['get', 'gets', 'all', 'add', 'update', 'delete'];

    /**
     * Operations to exclude
     *
     * @var array
     */
    public $exclude = [];
}
