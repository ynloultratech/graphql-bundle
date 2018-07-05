<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\DependencyInjection;

/**
 * Implement this interface on a service that need BC configuration.
 *
 * @internal
 */
interface BackwardCompatibilityAwareInterface
{
    /**
     * @param array $config
     */
    public function setBCConfig(array $config): void;
}
