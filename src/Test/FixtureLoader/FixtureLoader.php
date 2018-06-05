<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Test\FixtureLoader;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;
use Ynlo\GraphQLBundle\Test\FixtureLoader\DataPopulator\DataLoaderInterface;
use Ynlo\GraphQLBundle\Test\FixtureLoader\DataPopulator\ORMDataLoader;
use Ynlo\GraphQLBundle\Test\FixtureLoader\SchemaUpdater\ORMSQLite;
use Ynlo\GraphQLBundle\Test\FixtureLoader\SchemaUpdater\SchemaUpdaterInterface;

/**
 * Class FixtureLoader
 */
class FixtureLoader
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var array[]
     */
    protected $plugins = [];

    /**
     * @var bool
     */
    protected static $schemaUpdated = false;

    /**
     * FixtureLoader constructor.
     *
     * @param ContainerInterface $container
     * @param Registry           $registry
     * @param array              $plugins
     */
    public function __construct(ContainerInterface $container, Registry $registry, $plugins = [])
    {
        $this->registry = $registry;
        $this->container = $container;

        $this->plugins = $plugins;

        $cacheDir = $container->getParameter('kernel.cache_dir').DIRECTORY_SEPARATOR.'tests';
        $container->get('filesystem')->mkdir($cacheDir);

        $this->plugins[] = new ORMSQLite($cacheDir);
        $this->plugins[] = new ORMDataLoader($cacheDir);
    }

    /**
     * @param array $classNames
     * @param bool  $append
     *
     * @return ReferenceRepository
     */
    public function loadFixtures($classNames = [], $append = false): ReferenceRepository
    {
        if (!self::$schemaUpdated) {
            foreach ($this->plugins as $plugin) {
                if ($plugin instanceof SchemaUpdaterInterface) {
                    if ($plugin->supports($this->registry)) {
                        $plugin->updateSchema($this->registry);
                        self::$schemaUpdated = true;
                        break;
                    }
                }
            }
        }

        $loader = $this->getFixtureLoader($this->container, $classNames);
        $fixtures = $loader->getFixtures();
        $referenceRepository = new ProxyReferenceRepository($this->registry->getManager());
        foreach ($this->plugins as $plugin) {
            if ($plugin instanceof DataLoaderInterface) {
                if ($plugin->supports($this->registry)) {
                    $executor = $plugin->createExecutor($this->registry);
                    $executor->purge();
                    $executor->setReferenceRepository($referenceRepository);
                    $executor->execute($fixtures, $append);
                    break;
                }
            }
        }

        return $referenceRepository;
    }

    /**
     * Retrieve Doctrine DataFixtures loader.
     *
     * @param ContainerInterface $container
     * @param array              $classNames
     *
     * @return Loader
     */
    protected function getFixtureLoader(ContainerInterface $container, array $classNames)
    {
        $loaderClass = class_exists('Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader')
            ? 'Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader'
            : (class_exists('Doctrine\Bundle\FixturesBundle\Common\DataFixtures\Loader')
                // This class is not available during tests.
                // @codeCoverageIgnoreStart
                ? 'Doctrine\Bundle\FixturesBundle\Common\DataFixtures\Loader'
                // @codeCoverageIgnoreEnd
                : 'Symfony\Bundle\DoctrineFixturesBundle\Common\DataFixtures\Loader');

        /** @var Loader $loader */
        $loader = new $loaderClass($container);

        if ($classNames) {
            foreach ($classNames as $className) {
                $this->loadFixtureClass($loader, $className);
            }
        } else {
            $kernel = $container->get('kernel');
            $bundles = $kernel->getBundles();
            foreach ($bundles as $bundle) {
                $dir = $bundle->getPath().'/DataFixtures';
                if (file_exists($dir)) {
                    $loader->loadFromDirectory($dir);
                }
            }

            //load symfony4 data fixtures
            if (Kernel::VERSION_ID >= 40000) {
                $dir = $kernel->getRootDir().'/DataFixtures';
                if (file_exists($dir)) {
                    $loader->loadFromDirectory($dir);
                }
            }
        }

        return $loader;
    }

    /**
     * Load a data fixture class.
     *
     * @param Loader $loader
     * @param string $className
     */
    protected function loadFixtureClass($loader, $className)
    {
        $fixture = new $className();

        if ($loader->hasFixture($fixture)) {
            unset($fixture);

            return;
        }

        $loader->addFixture($fixture);

        if ($fixture instanceof DependentFixtureInterface) {
            foreach ($fixture->getDependencies() as $dependency) {
                $this->loadFixtureClass($loader, $dependency);
            }
        }
    }
}
