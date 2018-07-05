<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Filter\Resolver;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Ynlo\GraphQLBundle\Definition\ClassAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\ExecutableDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Filter\Common\NodeFilter;
use Ynlo\GraphQLBundle\Filter\FilterResolverInterface;

/**
 * Resolve filters automatically based on GraphQL field definitions directly related to Entity columns
 */
class DoctrineORMFilterResolver implements FilterResolverInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * DoctrineORMFilterResolver constructor.
     *
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @inheritDoc
     */
    public function resolve(ExecutableDefinitionInterface $executableDefinition,ObjectDefinitionInterface $node, Endpoint $endpoint): array
    {
        $class = $node->getClass();
        $filters = [];
        try {
            if (!$class || !($metaData = $this->manager->getClassMetadata($class))) {
                return $filters;
            }

            foreach ($node->getFields() as $field) {
                if (!$metaData->hasField($field->getName())
                    && !$metaData->hasAssociation($field->getName())
                    && !$metaData->hasField($field->getOriginName())
                    && !$metaData->hasAssociation($field->getOriginName())) {
                    continue;
                }

                //ignore ID
                if ($field->getName() === 'id') {
                    continue;
                }

                $filter = FilterUtil::createFilter($endpoint, $field);

                if (NodeFilter::class === $filter->resolver) {
                    try {
                        /** @var ClassAwareDefinitionInterface $relatedNode */
                        $relatedNode = $endpoint->getType($field->getType());
                        $relatedEntity = $relatedNode->getClass();
                        if ($this->manager->getClassMetadata($relatedEntity)) {
                            $associationType = null;

                            if ($metaData->hasAssociation($field->getName())) {
                                $associationType = $metaData->getAssociationMapping($field->getName())['type'] ?? null;
                            } elseif ($metaData->hasAssociation($field->getOriginName())) {
                                $associationType = $metaData->getAssociationMapping($field->getOriginName())['type'] ?? null;
                            }

                            //ignore filters by records only related to one record
                            if (\in_array($associationType, [ClassMetadataInfo::ONE_TO_ONE, ClassMetadataInfo::ONE_TO_MANY], true)) {
                                continue;
                            }
                        }
                    } catch (MappingException $exception) {
                        //ignore
                    }
                }

                if ($filter->resolver) {
                    $filters[] = $filter;
                }
            }

            return $filters;
        } catch (MappingException $exception) {
            return [];
        }
    }
}
