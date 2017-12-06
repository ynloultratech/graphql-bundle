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

use Doctrine\Common\Collections\Collection;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Ynlo\GraphQLBundle\Model\ID;

/**
 * Class ObjectFieldResolver
 */
class ObjectFieldResolver
{
    /**
     * @var FieldsAwareDefinitionInterface
     */
    protected $definition;

    /**
     * ObjectFieldResolver constructor.
     *
     * @param FieldsAwareDefinitionInterface $definition
     */
    public function __construct(FieldsAwareDefinitionInterface $definition)
    {
        $this->definition = $definition;
    }

    /**
     * @param mixed       $root
     * @param array       $args
     * @param mixed       $context
     * @param ResolveInfo $info
     *
     * @return mixed|null|string
     */
    public function __invoke($root, array $args, $context, ResolveInfo $info)
    {
        $value = null;
        $fieldDefinition = $this->definition->getField($info->fieldName);

        //array
        if (\is_array($root)) {
            if (isset($root[$fieldDefinition->getOriginName()])) {
                $value = $root[$fieldDefinition->getOriginName()];
            } elseif (isset($root[$info->fieldName])) {
                $value = $root[$info->fieldName];
            }
        }

        //method
        if (!$value && $fieldDefinition->getOriginType() === \ReflectionMethod::class) {
            $method = $fieldDefinition->getOriginName();
            $value = $root->$method();
        }

        //property
        if (!$value && \is_object($root)) {
            //using getter
            $accessor = new PropertyAccessor();
            $propertyName = $fieldDefinition->getOriginName() ?? $info->fieldName;
            if ($accessor->isReadable($root, $propertyName)) {
                $value = $accessor->getValue($root, $propertyName);
            } elseif ($this->definition instanceof ObjectDefinition) {
                if ($this->definition->getClass() && $fieldDefinition->getOriginName()) {
                    $class = $this->definition->getClass();
                } else {
                    $class = \get_class($root);
                }
                //using reflection
                $ref = new \ReflectionProperty($class, $propertyName);
                $ref->setAccessible(true);

                $value = $ref->getValue($root);
            }
        }

        if (null !== $value && Type::ID === $fieldDefinition->getType()) {
            //ID are formed with base64 representation of the Types and real database ID
            //in order to create a unique and global identifier for each resource
            //@see https://facebook.github.io/relay/docs/graphql-object-identification.html
            if ($value instanceof ID) {
                $value = (string) $value;
            } else {
                $value = (string) new ID($this->definition->getName(), $value);
            }
        }

        if ($value instanceof Collection) {
            $value = $value->toArray();
        }

        return $value;
    }
}
