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
use Ynlo\GraphQLBundle\Model\OrderBy;
use Ynlo\GraphQLBundle\Query\Node\AllNodes;

/**
 * Resolve queries
 */
class QueryGetAllNodesAnnotationParser implements AnnotationParserInterface
{
    use AnnotationReaderAwareTrait;
    use AnnotationParserHelper;

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
    public function parse($annotation, \ReflectionClass $refClass, Endpoint $endpoint)
    {
        /** @var Annotation\QueryGetAll $annotation */
        if ($annotation->name) {
            $name = $annotation->name;
        } else {
            $name = 'all'.Inflector::pluralize(ucfirst($this->getDefaultName($refClass, $endpoint)));
        }

        $query = new QueryDefinition();
        $query->setName($name);
        $query->setResolver(AllNodes::class);

        $objectDefinition = null;
        $typeName = null;
        if ($objectType = $this->reader->getClassAnnotation($refClass, Annotation\ObjectType::class)) {
            $typeName = $endpoint->getTypeForClass($refClass->getName());
            $query->setType($typeName);
            $query->setList(true);
            $query->setMeta('node', $typeName);
        }

        if ($annotation->pagination) {
            $query->setMeta('pagination', ['target' => $typeName]);
        }

        $orderBy = new ArgumentDefinition();
        $orderBy->setName('orderBy');
        $orderBy->setType(OrderBy::class);
        $orderBy->setNonNull(false);
        $orderBy->setList(true);
        $orderBy->setDescription('Ordering options for this list.');
        $query->addArgument($orderBy);
        $endpoint->addQuery($query);
    }
}
