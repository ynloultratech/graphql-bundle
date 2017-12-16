<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Loader;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Types\Type;
use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType as Fresh_AbstractEnumType;
use Ynlo\GraphQLBundle\Definition\EnumDefinition;
use Ynlo\GraphQLBundle\Definition\EnumValueDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Doctrine\DBAL\Types\AbstractEnumType;
use Ynlo\GraphQLBundle\Util\TypeUtil;

/**
 * DoctrineEnumLoader
 */
class DoctrineEnumLoader implements DefinitionLoaderInterface
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritDoc}
     */
    public function loadDefinitions(Endpoint $endpoint)
    {
        if (!class_exists('\Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType')) {
            return;
        }

        //registry connection should be called to
        //force doctrine load all registered DBAL types
        $this->registry->getConnection();
        $types = Type::getTypesMap();
        foreach ($types as $name => $class) {
            /** @var $class AbstractEnumType */
            if (is_subclass_of($class, Fresh_AbstractEnumType::class, true)) {
                $enum = new EnumDefinition();
                $enum->setName(TypeUtil::normalizeName($name));
                $enum->setClass($class);
                foreach ($class::getValues() as $value) {
                    $enumValue = new EnumValueDefinition();
                    $enumValue->setValue($value);
                    $enumValue->setName($value);

                    if (is_subclass_of($class, AbstractEnumType::class, true)) {
                        $enumValue->setName($class::getPublicName($value) ?: $value);
                        $enumValue->setDescription($class::getDescription($value) ?: null);
                        $enumValue->setDeprecationReason($class::getDeprecatedReason($value) ?: null);
                    }

                    $enum->addValue($enumValue);
                }

                $endpoint->addType($enum);
            }
        }
    }
}
