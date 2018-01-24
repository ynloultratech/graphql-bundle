<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Test;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Symfony\Bundle\FrameworkBundle\Client;
use Ynlo\GraphQLBundle\Test\FixtureLoader\FixtureLoader;

/**
 * Trait DataFixtureTrait
 */
trait DataFixtureTrait
{
    /**
     * @var ReferenceRepository
     */
    protected static $referenceRepository;

    /**
     * @param array $classNames
     */
    public static function loadFixtures($classNames = [])
    {
        /** @var Client $client */
        $client = static::getClient();
        $container = $client->getContainer();
        if ($container) {
            $fixtureLoader = new FixtureLoader($container, $container->get('doctrine'));
            static::$referenceRepository = $fixtureLoader->loadFixtures($classNames);
        }
    }

    public static function getFixtureReference(string $name)
    {
        return self::$referenceRepository->getReference($name);
    }
}
