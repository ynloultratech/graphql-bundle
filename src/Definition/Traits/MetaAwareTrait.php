<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Traits;

use Ynlo\GraphQLBundle\Annotation\Plugin\PluginConfigAnnotation;
use Ynlo\GraphQLBundle\Definition\MetaAwareInterface;

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

        //convert pluginAnnotation meta to correct key:value pair
        foreach ($this->metas as $index => $meta) {
            if ($meta instanceof PluginConfigAnnotation) {
                unset($this->metas[$index]);
                $this->setMeta($meta->getName(), $meta->getConfig());
            }
        }

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
        //convert pluginAnnotation meta to correct key:value pair
        if ($value instanceof PluginConfigAnnotation) {
            $key = $value->getName();
            $value = $value->getConfig();
        }

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
