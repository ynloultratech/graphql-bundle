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

use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;
use Ynlo\GraphQLBundle\Model\NodeConnection;
use Ynlo\GraphQLBundle\Model\OrderBy;
use Ynlo\GraphQLBundle\Query\Node\AllNodes;

/**
 * Create a connection definition for given query or field definition
 */
class ConnectionDefinitionBuilder
{
    /**
     * @var DefinitionRegistry
     */
    protected $definitionRegistry;

    /**
     * @var string
     */
    protected $endpoint = 'default';

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var string
     */
    protected $parentField;

    /**
     * @var string|null
     */
    protected $deprecationReason;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * ConnectionDefinitionBuilder constructor.
     *
     * @param DefinitionRegistry $definitionRegistry
     * @param int                $limit
     */
    public function __construct(DefinitionRegistry $definitionRegistry, $limit = 100)
    {
        $this->definitionRegistry = $definitionRegistry;

        //TODO: resolve default limit from bundle global config
        $this->limit = $limit;
    }

    /**
     * @param string $endpoint
     */
    public function setEndpoint(string $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @param int|null $limit
     */
    public function setLimit(?int $limit)
    {
        $this->limit = $limit ?? $this->limit;
    }

    /**
     * @param string $parentField
     */
    public function setParentField(?string $parentField)
    {
        $this->parentField = $parentField;
    }

    /**
     * @param null|string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param null|string $deprecationReason
     */
    public function setDeprecationReason($deprecationReason)
    {
        $this->deprecationReason = $deprecationReason;
    }

    /**
     * @param ExecutableDefinitionInterface $definition
     * @param string                        $node
     *
     * @throws \UnexpectedValueException if given node type is not valid
     */
    public function build(ExecutableDefinitionInterface $definition, string $node)
    {
        $definitionManager = $this->definitionRegistry->getManager($this->endpoint);

        if (class_exists($node) || interface_exists($node)) {
            $node = $definitionManager->getTypeForClass($node);
        }

        $objectDefinition = $definitionManager->getType($node);

        if ($definition instanceof FieldDefinition) {
            if ($objectDefinition->hasField($definition->getName())) {
                $definition = $objectDefinition->getField($definition->getName());
            } else {
                $objectDefinition->addField($definition);
            }
        }

        if ($definition instanceof QueryDefinition && !$definitionManager->hasQuery($definition->getName())) {
            $definitionManager->addQuery($definition);
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

        $orderBy = new ArgumentDefinition();
        $orderBy->setName('orderBy');
        $orderBy->setType(OrderBy::class);
        $orderBy->setNonNull(false);
        $orderBy->setList(true);
        $orderBy->setDescription('Ordering options for this list.');
        $definition->addArgument($orderBy);

        $definition->setType(NodeConnection::class);
        $definition->setList(false);
        $definition->setMeta('node', $objectDefinition->getName());

        $definition->setMeta('connection_limit', $this->limit);

        if ($this->parentField) {
            $definition->setMeta('connection_parent_field', $this->parentField);
        }

        $definition->setResolver(AllNodes::class);
        $definition->setDeprecationReason($this->deprecationReason);
        $definition->setDescription($this->description);
    }
}
