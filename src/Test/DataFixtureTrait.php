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
use Doctrine\ORM\EntityManager;
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
        $client = self::createClient();
        $container = $client->getContainer();
        if ($container) {
            $fixtureLoader = new FixtureLoader($container, $container->get('doctrine'));
            self::$referenceRepository = $fixtureLoader->loadFixtures($classNames);
        }
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public static function getFixtureReference($name)
    {
        $reference = self::$referenceRepository->getReference($name);

        /** @var EntityManager $em */
        $em = self::getDoctrine()->getManager();
        $em->refresh($reference);

        return clone $reference;
    }
}
