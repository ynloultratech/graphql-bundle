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
 * Class MetaAwareTrait
 */
trait MetaAwareTrait
{
    /**
     * @var array
     */
    protected $metas = [];

    /**
     * @return array
     */
    public function getMetas(): array
    {
        return $this->metas;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getMeta(string $key)
    {
        return $this->metas[$key];
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasMeta(string $key): bool
    {
        return array_key_exists($key, $this->metas);
    }

    /**
     * @param array $metas
     *
     * @return MetaAwareInterface
     */
    public function setMetas(array $metas): MetaAwareInterface
    {
        $this->metas = $metas;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return MetaAwareInterface
     */
    public function setMeta(string $key, $value): MetaAwareInterface
    {
        $this->metas[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     *
     * @return MetaAwareInterface
     */
    public function removeMeta(string $key): MetaAwareInterface
    {
        unset($this->metas[$key]);

        return $this;
    }
}
