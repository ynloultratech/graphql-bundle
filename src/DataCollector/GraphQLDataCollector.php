<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Ynlo\GraphQLBundle\Definition\InputObjectDefinition;
use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

class GraphQLDataCollector extends DataCollector
{
    /**
     * @var DefinitionRegistry
     */
    protected $registry;

    /**
     * @var Endpoint
     */
    protected $endpoint;

    /**
     * GraphQLDataCollector constructor.
     *
     * @param DefinitionRegistry $registry
     */
    public function __construct(DefinitionRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'graphql.collector';
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->data = [];
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->endpoint = $this->registry->getEndpoint();
        $this->data = [
            'endpoint' => $this->endpoint,
        ];
    }

    /**
     * @return Endpoint
     */
    public function getEndpoint()
    {
        return $this->data['endpoint'];
    }

    public function isInputObject($definition)
    {
        return $definition instanceof InputObjectDefinition;
    }

    public function isInterface($definition)
    {
        return $definition instanceof InterfaceDefinition;
    }

    public function isObject($definition)
    {
        return $definition instanceof ObjectDefinition;
    }
}
