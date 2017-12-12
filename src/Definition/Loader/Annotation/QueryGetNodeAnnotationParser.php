<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Loader\Annotation;

use Doctrine\Common\Util\Inflector;
use Ynlo\GraphQLBundle\Annotation;
use Ynlo\GraphQLBundle\Definition\ArgumentDefinition;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Extension\ExtensionManager;
use Ynlo\GraphQLBundle\Query\Node\Node;
use Ynlo\GraphQLBundle\Query\Node\Nodes;

/**
 * Resolve queries
 */
class QueryGetNodeAnnotationParser implements AnnotationParserInterface
{
    use AnnotationReaderAwareTrait;
    use AnnotationParserHelper;

    /**
     * @var ExtensionManager
     */
    protected $extensionManager;

    /**
     * QueryGetAllNodesAnnotationParser constructor.
     *
     * @param ExtensionManager $extensionManager
     */
    public function __construct(ExtensionManager $extensionManager)
    {
        $this->extensionManager = $extensionManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($annotation): bool
    {
        return $annotation instanceof Annotation\QueryGet;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($annotation, \ReflectionClass $refClass, Endpoint $endpoint)
    {
        /** @var Annotation\QueryGet $annotation */
        if ($annotation->name) {
            $name = $annotation->name;
        } else {
            $name = lcfirst($this->getDefaultName($refClass, $endpoint));
        }
        $this->createGetNoneQuery($name, $annotation, $refClass, $endpoint);

        if ($annotation->pluralQuery) {
            if ($annotation->pluralQueryName) {
                $name = $annotation->pluralQueryName;
            } else {
                $name = Inflector::pluralize(lcfirst($this->getDefaultName($refClass, $endpoint)));
            }
            $this->createGetNoneQuery($name, $annotation, $refClass, $endpoint, true);
        }
    }

    /**
     * @param string           $name
     * @param mixed            $annotation
     * @param \ReflectionClass $refClass
     * @param Endpoint         $endpoint
     * @param bool             $plural
     */
    protected function createGetNoneQuery($name, $annotation, \ReflectionClass $refClass, Endpoint $endpoint, $plural = false)
    {
        /** @var Annotation\QueryGet $annotation */
        $query = new QueryDefinition();
        $query->setName($name);

        $objectDefinition = null;
        /** @var Annotation\ObjectType $objectType */
        if ($objectType = $this->reader->getClassAnnotation($refClass, Annotation\ObjectType::class)) {
            $typeName = $endpoint->getTypeForClass($refClass->getName());
            $objectDefinition = $endpoint->getType($typeName);
            $query->setMeta('node', $typeName);
        }

        $fetchBy = $annotation->fetchBy ?? 'id';

        if (!$objectDefinition->hasField($fetchBy)) {
            $error = sprintf('The object type "%s" does not have a field "%s" to fetch record using this field.', $objectDefinition->getName(), $fetchBy);
            throw new \InvalidArgumentException($error);
        }

        $fieldDefinition = $objectDefinition->getField($fetchBy);
        $argument = new ArgumentDefinition();
        $argument->setName($plural ? Inflector::pluralize($fieldDefinition->getName()) : $fieldDefinition->getName());
        $argument->setType($fieldDefinition->getType());
        $argument->setList($plural);
        $argument->setDescription($fieldDefinition->getDescription());
        $argument->setInternalName($plural ? 'ids' : 'id');
        $query->addArgument($argument);

        $query->setType($objectDefinition->getName());
        $query->setList($plural);
        $query->setResolver($plural ? Nodes::class : Node::class);
        $query->setDeprecationReason($annotation->deprecationReason);
        $query->setDescription($annotation->description);

        foreach ($this->extensionManager->getExtensions() as $extension) {
            $extension->configureDefinition($query, $refClass, $endpoint);
        }

        $endpoint->addQuery($query);
    }
}
