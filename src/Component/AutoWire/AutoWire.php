<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Component\AutoWire;

use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Based on the principle of symfony autowiring (https://symfony.com/doc/current/service_container/autowiring.html)
 * this autoWire is used to create a instance and inject dependencies using constructor of given class.
 *
 * - Remove the need of register service for simple classes, like resolvers
 * - Does not impact the performance in dev or prod
 *
 * Caveats:
 * - Only works in a constructor
 * - Only works if the constructor typehint match exactly with desired service or the param name match with the service or parameter name
 *
 * How it works?
 *
 * Services:
 *
 * - Firstly try to find a service based on constructor type hint
 * - If not exist any service registered with this type, then try to find using the service name based on argument name
 *    Service Name Conventions:
 *      $service => '@service'
 *      $serviceName => '@service_name'
 *      $serviceName_WithDot => '@service_name.with_dot'
 *
 * NOTE: in any case, services require ALWAYS the type in the constructor.
 *
 * Parameters:
 * - If the type hint of the argument is not defined or is any scalar type
 * a parameter will be injected using the same naming convention of services
 */
class AutoWire implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param string $class
     *
     * @return mixed
     */
    public function createInstance(string $class)
    {
        $refClass = new \ReflectionClass($class);
        $args = [];
        if ($refClass->getConstructor()) {
            foreach ($refClass->getConstructor()->getParameters() as $parameter) {
                $name = Inflector::tableize(str_replace('_', '.', $parameter->getName()));
                $dependency = null;
                if ($parameter->getClass()) {
                    if ($this->container->has($parameter->getClass()->getName())) {
                        $dependency = $this->container->get($parameter->getClass()->getName());
                    } elseif ($this->container->has($name)) {
                        $dependency = $this->container->get($name);
                    }
                } elseif ($this->container->hasParameter($name)) {
                    $dependency = $this->container->getParameter($name);
                }
                $args[] = $dependency;
            }
        }

        return $refClass->newInstanceArgs($args);
    }
}
