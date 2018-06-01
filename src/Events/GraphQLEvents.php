<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Events;

final class GraphQLEvents
{
    /**
     * Private constructor. This class is not meant to be instantiated.
     */
    private function __construct()
    {
    }

    /**
     * This event is executed just before a operation is started
     * event: Ynlo\GraphQLBundle\Events\GraphQLOperationEvent
     *
     * @var string
     */
    public const OPERATION_START = 'graphql.operationStart';

    /**
     * This event is executed just after a operation is finished
     * event: Ynlo\GraphQLBundle\Events\GraphQLOperationEvent
     *
     * @var string
     */
    public const OPERATION_END = 'graphql.operationEnd';

    /**
     * This event is executed just before a field has been read
     * if the propagation is stopped or the value is settled in the event
     * the las value will be returned
     *
     * event: Ynlo\GraphQLBundle\Events\GraphQLFieldEvent
     *
     * @var string
     */
    public const PRE_READ_FIELD = 'graphql.preGetField';

    /**
     * This event is executed just after a field has been read
     * if the propagation is stopped or the value is settled in the event
     * the las value will be returned
     *
     * event: Ynlo\GraphQLBundle\Events\GraphQLFieldEvent
     *
     * @var string
     */
    public const POST_READ_FIELD = 'graphql.postGetField';

    /**
     * This event is executed when a mutation is submitted
     * event: Ynlo\GraphQLBundle\Events\GraphQLMutationEvent
     *
     * @var string
     */
    public const MUTATION_SUBMITTED = 'graphql.mutationSubmitted';

    /**
     * This event is executed when a mutation is completed
     * event: Ynlo\GraphQLBundle\Events\GraphQLMutationEvent
     *
     * @var string
     */
    public const MUTATION_COMPLETED = 'graphql.mutationCompleted';
}
