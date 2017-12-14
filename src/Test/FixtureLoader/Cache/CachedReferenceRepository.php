<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Test\FixtureLoader\Cache;

use Doctrine\Common\DataFixtures\ReferenceRepository;

/**
 * CachedReferenceRepository
 */
class CachedReferenceRepository extends ReferenceRepository
{
    private static $oids = [];

    private static $references = [];

    private static $identities = [];

    /**
     * @param string $oid
     * @param string $reference
     */
    public function setReferenceByObjectId($oid, $reference)
    {
        foreach (self::$oids as $name => $originalId) {
            if ($oid === $originalId) {
                self::$references[$name] = $reference;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setReference($name, $reference)
    {
        if (!isset(self::$references[$name])) {
            parent::setReference($name, $reference);
            self::$references[$name] = $reference;
            self::$oids[$name] = spl_object_hash($reference);
        }
    }

    /**
     *{@inheritDoc}
     */
    public function setReferenceIdentity($name, $identity)
    {
        if (!isset(self::$identities[$name])) {
            parent::setReferenceIdentity($name, $identity);
            self::$identities[$name] = $identity;
        }
    }

    /**
     *{@inheritDoc}
     */
    public function addReference($name, $object)
    {
        if (!isset(self::$references[$name])) {
            parent::addReference($name, $object);
            self::$references[$name] = $object;
        }
    }

    /**
     *{@inheritDoc}
     */
    public function getReference($name)
    {
        if (!isset(self::$references[$name])) {
            self::$references[$name] = parent::getReference($name);
        }

        return self::$references[$name];
    }

    /**
     *{@inheritDoc}
     */
    public function getReferences()
    {
        return self::$references;
    }
}
