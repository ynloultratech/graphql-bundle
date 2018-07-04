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

use Doctrine\Common\Util\Inflector;

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

        //set default values
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($this);
            if (null !== $value) {
                $this->config[Inflector::tableize($property->getName())] = $property->getValue($this);
            }
        }

        //if only one value is given, the first property is set with the given value
        if (isset($config['value']) && count($config) === 1) {
            if ($properties) {
                $propName = $ref->getProperties()[0]->getName();
                $this->$propName = $config['value'];
                $this->config[Inflector::tableize($propName)] = $config['value'];
            }
        } else {
            foreach ($config as $key => $value) {
                //dynamically added to avoid ide auto-complete for this property
                $this->config[Inflector::tableize($key)] = $value;
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
        return $this->config ?? [];
    }

    /**
     * Must return the plugin name to apply this config
     *
     * @return string
     */
    public function getName(): string
    {
        preg_match('/\w+$/', get_class($this), $matches);

        return Inflector::tableize($matches[0]);
    }
}
