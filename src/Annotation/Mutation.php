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

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation()
 *
 * @Target("CLASS")
 */
class Mutation
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $form;

    /**
     * @var array
     */
    public $formOptions = [];

    /**
     * @var bool
     */
    public $clientMutationId = true;

    /**
     * @var bool
     */
    public $dryRun;

    /**
     * @var string
     */
    public $resolver;

    /**
     * @Required()
     */
    public $payload;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $deprecationReason;
}
