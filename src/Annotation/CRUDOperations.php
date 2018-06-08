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

@trigger_error('@CRUDOperations annotation has been deprecated and will be removed in v2.0, use specific crud annotations', E_USER_DEPRECATED);

/**
 * @Annotation()
 *
 * @deprecated [<2.0] use QueryList, MutationAdd, MutationUpdate, MutationDelete annotations instead.
 */
class CRUDOperations
{
    /**
     * @var \Ynlo\GraphQLBundle\Annotation\Query
     */
    public $list;

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
    public $include = ['list', 'add', 'update', 'delete'];

    /**
     * Operations to exclude
     *
     * @var array
     */
    public $exclude = [];
}
