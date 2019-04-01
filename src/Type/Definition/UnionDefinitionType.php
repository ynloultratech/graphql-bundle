<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Type\Definition;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\Definition\UnionDefinition;
use Ynlo\GraphQLBundle\Definition\UnionTypeDefinition;
use Ynlo\GraphQLBundle\Type\Registry\TypeRegistry;
use Ynlo\GraphQLBundle\Util\TypeUtil;

class UnionDefinitionType extends UnionType implements ContainerAwareInterface, EndpointAwareInterface
{
    use ContainerAwareTrait;
    use EndpointAwareTrait;

    public function __construct(UnionDefinition $definition)
    {
        parent::__construct(
            [
                'name' => $definition->getName(),
                'description' => $definition->getDescription(),
                'types' => function () use ($definition) {
                    $types = [];
                    $unionTypes = $definition->getTypes();

                    // expand interface types
                    foreach ($unionTypes as $index => $unionType) {
                        $typeDefinition = $this->endpoint->getType($unionType->getType());
                        if ($typeDefinition instanceof InterfaceDefinition) {
                            $interfaceTypes = $typeDefinition->getImplementors();
                            unset($unionTypes[$index]);
                            foreach ($interfaceTypes as $interfaceType) {
                                $newUnionType = new UnionTypeDefinition();
                                $newUnionType->setType($interfaceType);
                                $newUnionType->setList($unionType->isList());
                                $newUnionType->setNonNull($unionType->isNonNull());
                                $newUnionType->setNonNullList($unionType->isNonNullList());
                                $unionTypes[] = $newUnionType;
                            }
                        }
                    }

                    foreach ($unionTypes as $unionType) {
                        $type = TypeRegistry::get($unionType->getType());
                        if ($unionType->isList()) {
                            if ($unionType->isNonNullList()) {
                                $type = Type::nonNull($type);
                            }
                            $type = Type::listOf($type);
                        }

                        if ($unionType->isNonNull()) {
                            $type = Type::nonNull($type);
                        }

                        $types[] = $type;
                    }

                    return $types;
                },
                'resolveType' => function ($value) {
                    $type = TypeUtil::resolveObjectType($this->endpoint, $value);
                    if (!$type) {
                        throw new \RuntimeException(sprintf('Can`t resolve a valid type for object of class %s.', get_class($value)));
                    }

                    return TypeRegistry::get($type);
                },
            ]
        );
    }
}
