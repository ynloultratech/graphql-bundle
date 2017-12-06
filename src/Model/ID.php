<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Model;

/**
 * ID are formed with base64 representation of the Types and real database ID
 * in order to create a unique and global identifier for each resource
 *
 * @see https://facebook.github.io/relay/docs/graphql-object-identification.html
 */
class ID
{
    private const DIVIDER = ':';

    /**
     * @var mixed
     */
    protected $databaseId;

    /**
     * @var string
     */
    protected $nodeType;

    /**
     * ID constructor.
     *
     * @param string $nodeType
     * @param mixed  $databaseId
     */
    public function __construct($nodeType, $databaseId)
    {
        $this->databaseId = $databaseId;
        $this->nodeType = $nodeType;
    }

    /**
     * @param string $nodeType
     * @param mixed  $databaseId
     *
     * @return ID
     */
    public static function create($nodeType, $databaseId): ID
    {
        return new self($nodeType, $databaseId);
    }

    /**
     * @param string $globalIdentifier
     *
     * @return ID
     */
    public static function createFromString($globalIdentifier)
    {
        $typeAndUser = base64_decode($globalIdentifier);
        if (strpos($typeAndUser, self::DIVIDER) > 1) {
            list($nodeType, $databaseId) = explode(self::DIVIDER, $typeAndUser);
        } else {
            $nodeType = null;
            $databaseId = null;
        }

        return new self($nodeType, $databaseId);
    }

    /**
     * @param string $nodeType
     * @param mixed  $databaseId
     *
     * @return string
     */
    public static function encode($nodeType, $databaseId)
    {
        return base64_encode(sprintf('%s%s%s', $nodeType, self::DIVIDER, $databaseId));
    }

    /**
     * @return mixed
     */
    public function getDatabaseId()
    {
        return $this->databaseId;
    }

    /**
     * @return string
     */
    public function getNodeType(): ?string
    {
        return $this->nodeType;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return self::encode($this->getNodeType(), $this->getDatabaseId());
    }
}
