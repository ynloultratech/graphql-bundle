<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Extension;

use Doctrine\Common\Annotations\Reader;
use Ynlo\GraphQLBundle\Annotation as GraphqQL;
use Ynlo\GraphQLBundle\Definition\ArgumentDefinition;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionManager;
use Ynlo\GraphQLBundle\Model\ConnectionInterface;
use Ynlo\GraphQLBundle\Model\OrderBy;
use Ynlo\GraphQLBundle\Query\Node\AllNodesConnection;

/**
 * Convert a simple return of nodes into a paginated collection with edges
 */
class PaginationExtension extends AbstractGraphQLExtension
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var int
     */
    protected $limit;

    /**
     * PaginationExtension constructor.
     *
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
        $this->limit = 100; //TODO: resolve from some global bundle config
    }

    /**
     * {@inheritdoc}
     */
    public function configureDefinition(DefinitionInterface $definition, \ReflectionClass $refClass, DefinitionManager $definitionManager)
    {
        /** @var GraphqQL\Connection $connection */
        $connection = null;

        if ($definition instanceof FieldDefinition && !$refClass->hasMethod('__invoke')) {
            if ($definition->getOriginType() === \ReflectionMethod::class && $refClass->hasMethod($definition->getOriginName())) {
                $connection = $this->reader->getMethodAnnotation($refClass->getMethod($definition->getOriginName()), GraphqQL\Connection::class);
            } elseif ($definition->getOriginType() === \ReflectionProperty::class && $refClass->hasProperty($definition->getOriginName())) {
                $connection = $this->reader->getPropertyAnnotation($refClass->getProperty($definition->getOriginName()), GraphqQL\Connection::class);
            }

            if ($connection && !$connection->parentField && $definition->hasMeta('connection_parent_field')) {
                $connection->parentField = $definition->getMeta('connection_parent_field');
            }
        } else {
            $connection = $this->reader->getClassAnnotation($refClass, GraphqQL\Connection::class);

            /** @var GraphqQL\QueryGetAll $queryGetAll */
            $queryGetAll = $this->reader->getClassAnnotation($refClass, GraphqQL\QueryGetAll::class);
            if (!$connection && $queryGetAll) {
                $connection = new GraphqQL\Connection();
            }

            if ($connection) {
                $connection->limit = $connection->limit ?? ($queryGetAll->limit ?? $this->limit);
            }
        }

        $node = null;
        if ($definition instanceof QueryDefinition) {
            $node = $definition->getType();
        } elseif ($definition instanceof FieldDefinition) {
            $node = $definition->getType();
        }

        if (class_exists($node) || interface_exists($node)) {
            if ($definitionManager->hasTypeForClass($node)) {
                $node = $definitionManager->getTypeForClass($node);
            }
        }

        if (!$definitionManager->hasType($node)) {
            $node = null;
        }

        if (!$connection || !$node) {
            return;
        }

        $objectDefinition = $definitionManager->getType($node);

        if ($definition instanceof FieldDefinition) {
            if ($objectDefinition->hasField($definition->getName())) {
                $definition = $objectDefinition->getField($definition->getName());
            } else {
                $objectDefinition->addField($definition);
            }
        }

        $first = new ArgumentDefinition();
        $first->setName('first');
        $first->setType('int');
        $first->setNonNull(false);
        $first->setDescription('Returns the first *n* elements from the list.');
        $definition->addArgument($first);

        $last = new ArgumentDefinition();
        $last->setName('last');
        $last->setType('int');
        $last->setNonNull(false);
        $last->setDescription('Returns the last *n* elements from the list.');
        $definition->addArgument($last);

        $after = new ArgumentDefinition();
        $after->setName('after');
        $after->setType('string');
        $after->setNonNull(false);
        $after->setDescription('Returns the last *n* elements from the list.');
        $definition->addArgument($after);

        $before = new ArgumentDefinition();
        $before->setName('before');
        $before->setType('string');
        $before->setNonNull(false);
        $before->setDescription('Returns the last *n* elements from the list.');
        $definition->addArgument($before);

        if (!$definition->hasArgument('orderBy')) {
            $orderBy = new ArgumentDefinition();
            $orderBy->setName('orderBy');
            $orderBy->setType(OrderBy::class);
            $orderBy->setNonNull(false);
            $orderBy->setList(true);
            $orderBy->setDescription('Ordering options for this list.');
            $definition->addArgument($orderBy);
        } else {
            //if exist move to the end
            $orderBy = $definition->getArgument('orderBy');
            $definition->removeArgument('orderBy');
            $definition->addArgument($orderBy);
        }

        $definition->setType(ConnectionInterface::class);
        $definition->setList(false);
        $definition->setMeta('node', $objectDefinition->getName());

        $definition->setMeta('connection_limit', $connection->limit ?? $this->limit);

        if ($connection->parentField) {
            $definition->setMeta('connection_parent_field', $connection->parentField);
        }

        if (!$refClass->hasMethod('__invoke')) {
            $definition->setResolver(AllNodesConnection::class);
        }
    }
}
