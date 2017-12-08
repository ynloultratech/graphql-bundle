<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition;

/**
 * Interface MetaAwareInterface
 */
interface MetaAwareInterface
{
    /**
     * @return ArgumentDefinition[]
     */
    public function getMetas(): array;

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getMeta(string $key);

    /**
     * @param string $key
     *
     * @return boolean
     */
    public function hasMeta(string $key): bool;

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return MetaAwareInterface
     */
    public function setMeta(string $key, $value): MetaAwareInterface;

    /**
     * @param string $key
     *
     * @return MetaAwareInterface
     */
    public function removeMeta(string $key): MetaAwareInterface;
}