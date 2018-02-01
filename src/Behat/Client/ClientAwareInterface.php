<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Client;

/**
 * ClientAwareInterface should be implemented by classes that depends on a Client.
 */
interface ClientAwareInterface
{
    /**
     * @param GraphQLClient $client
     *
     * @return ClientAwareInterface
     */
    public function setClient(GraphQLClient $client): ClientAwareInterface;
}
