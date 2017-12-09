<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\DefinitionLoader\DefinitionResolver;

use Doctrine\Common\Util\Inflector;
use Ynlo\GraphQLBundle\Annotation;
use Ynlo\GraphQLBundle\Definition\ConnectionDefinitionBuilder;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\DefinitionLoader\DefinitionManager;

/**
 * Resolve queries
 */
class QueryGetAllNodes implements DefinitionResolverInterface
{
    use AnnotationReaderAwareTrait;
    use ObjectQueryTrait;

    /**
     * @var ConnectionDefinitionBuilder
     */
    protected $connectionBuilder;

    /**
     * QueryGetAllNodes constructor.
     *
     * @param ConnectionDefinitionBuilder $connectionBuilder
     */
    public function __construct(ConnectionDefinitionBuilder $connectionBuilder)
    {
        $this->connectionBuilder = $connectionBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($annotation): bool
    {
        return $annotation instanceof Annotation\QueryGetAll;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($annotation, \ReflectionClass $refClass, DefinitionManager $definitionManager)
    {
        /** @var Annotation\QueryGetAll $annotation */
        if ($annotation->name) {
            $name = $annotation->name;
        } else {
            $name = 'all'.Inflector::pluralize(ucfirst($this->getDefaultName($refClass, $definitionManager)));
        }

        $query = new QueryDefinition();
        $query->setName($name);

        $objectDefinition = null;
        $typeName = null;
        if ($objectType = $this->reader->getClassAnnotation($refClass, Annotation\ObjectType::class)) {
            $typeName = $definitionManager->getTypeForClass($refClass->getName());
        }

        $this->connectionBuilder->setEndpoint($definitionManager->getEndpoint());
        $this->connectionBuilder->setLimit($annotation->limit);
        $this->connectionBuilder->setDeprecationReason($annotation->deprecationReason);
        $this->connectionBuilder->setDescription($annotation->description);
        $this->connectionBuilder->build($query, $typeName);
    }
}
