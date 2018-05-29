<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Test\FixtureLoader\DataPopulator;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * This executor save serialized fixture references and copy
 * of database using a unique hash for fixtures and schema
 */
class SQLiteORMExecutor extends ORMExecutor
{
    private $database;

    protected $cacheDir;

    public function __construct(EntityManagerInterface $em, ORMPurger $purger = null, $cacheDir = null)
    {
        parent::__construct($em, $purger);

        $this->cacheDir = $cacheDir;

        $params = $em->getConnection()->getParams();
        if (isset($params['master'])) {
            $params = $params['master'];
        }

        $this->database = $params['path'] ?? ($params['dbname'] ?? null);
    }

    /**
     * @inheritDoc
     */
    public function purge()
    {
        if (!$this->cacheDir) {
            parent::purge();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function execute(array $fixtures, $append = false)
    {
        $repo = $this->getReferenceRepository();

        if ($this->cacheDir && $repo instanceof ProxyReferenceRepository) {
            static $hash = null;
            if (!$hash) {
                $hash = $this->buildHash($fixtures);
            }

            if ($repo->load($this->getDataCacheFile($hash))) {
                copy($this->getDataCacheFile($hash), $this->database);

                return;
            }
        }

        parent::execute($fixtures, $append);

        if (isset($hash)) {
            $repo->save($this->getDataCacheFile($hash));

            copy($this->database, $this->getDataCacheFile($hash));
        }
    }

    protected function buildHash($fixtures): string
    {
        $om = $this->getObjectManager();
        $metadata = $om->getMetadataFactory()->getAllMetadata();
        $hash = md5(serialize($metadata));

        foreach ($fixtures as $fixture) {
            $ref = new \ReflectionClass(get_class($fixture));
            $file = new File($ref->getFileName());
            $hash .= $file->getMTime();
        }

        return md5($hash);
    }

    public function getDataCacheFile($hash): string
    {
        return $this->cacheDir.DIRECTORY_SEPARATOR.$hash.'.data';
    }
}
