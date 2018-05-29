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

class ORMSQLite implements SchemaUpdaterInterface
{
    protected $cacheDir;

    public function __construct(string $cacheDir = null)
    {
        $this->cacheDir = $cacheDir;
    }

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
            throw new \InvalidArgumentException("Connection does not contain a 'path' or 'dbname' parameter and cannot be dropped.");
        }

        $om = $registry->getManager();
        static $metadata = null;
        static $metadataHash = null;
        if (!$metadata) {
            $metadata = $om->getMetadataFactory()->getAllMetadata();
            $metadataHash = md5(serialize($metadata));
        }

        if ($this->cacheDir && $this->cacheExist($metadataHash)) {
            copy($this->getCacheFile($metadataHash), $name);

            return;
        }

        $schemaTool = new SchemaTool($om);
        $schemaTool->dropDatabase();
        if (!empty($metadata)) {
            $schemaTool->createSchema($metadata);
        }

        if ($this->cacheDir) {
            copy($name, $this->getCacheFile($metadataHash));
        }
    }

    public function getCacheFile($hash): string
    {
        return $this->cacheDir.DIRECTORY_SEPARATOR.$hash.'.schema';
    }

    public function cacheExist($hash): bool
    {
        return file_exists($this->getCacheFile($hash));
    }
}
