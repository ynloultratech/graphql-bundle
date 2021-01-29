<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Annotation\Plugin;

use Doctrine\Inflector\InflectorFactory;

/**
 * Can use this annotation as base to se plugin options.
 * Override and define all plugin settings.
 */
abstract class PluginConfigAnnotation
{
    public function __construct(array $config = [])
    {
        $ref = new \ReflectionClass(get_class($this));
        $properties = $ref->getProperties();

        //if only one value is given, the first property is set with the given value
        if ($properties && isset($config['value']) && \count($config) === 1) {
            $propName = $properties[0]->getName();
            $this->$propName = $config['value'];
        } else {
            foreach ($config as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Must return the array with plugin config
     *
     * @return array
     */
    public function getConfig(): array
    {
        $ref = new \ReflectionClass(get_class($this));
        $properties = $ref->getProperties();
        $config = [];

        //set default values
        foreach ($properties as $property) {
            $value = $property->getValue($this);
            if (null !== $value) {
                $config[InflectorFactory::create()->build()->tableize($property->getName())] = $property->getValue($this);
            }
        }

        return $config;
    }

    /**
     * Must return the plugin name to apply this config
     *
     * @return string
     */
    public function getName(): string
    {
        preg_match('/\w+$/', get_class($this), $matches);

        return InflectorFactory::create()->build()->tableize($matches[0]);
    }
}
