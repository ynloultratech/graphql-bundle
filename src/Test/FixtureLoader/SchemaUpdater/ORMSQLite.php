<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Test\FixtureLoader\SchemaUpdater;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Class ORMSQLite
 */
class ORMSQLite implements SchemaUpdaterInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Registry $registry)
    {
        return $registry->getName() === 'ORM'
               && ($connection = $registry->getConnection())
               && $connection instanceof Connection
               && $connection->getDriver() instanceof Driver\AbstractSQLiteDriver;
    }

    /**
     * {@inheritdoc}
     */
    public function updateSchema(Registry $registry)
    {
        $params = $registry->getConnection()->getParams();
        if (isset($params['master'])) {
            $params = $params['master'];
        }

        $name = $params['path'] ?? ($params['dbname'] ?? false);
        if (!$name) {
            throw new \InvalidArgumentException("PaginatedConnection does not contain a 'path' or 'dbname' parameter and cannot be dropped.");
        }

        $om = $registry->getManager();
        $metadata = $om->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($om);
        $schemaTool->dropDatabase();
        if (!empty($metadata)) {
            $schemaTool->createSchema($metadata);
        }
    }
}
