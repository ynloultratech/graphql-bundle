<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Loader;

use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

/**
 * Interface DefinitionLoaderInterface
 */
interface DefinitionLoaderInterface
{
    /**
     * @param Endpoint $endpoint
     */
    public function loadDefinitions(Endpoint $endpoint);
}
