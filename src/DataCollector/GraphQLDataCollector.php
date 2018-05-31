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
use Symfony\Component\VarDumper\Cloner\Data;
use Ynlo\GraphQLBundle\Definition\InputObjectDefinition;
use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Extension\EndpointNotValidException;
use Ynlo\GraphQLBundle\Security\EndpointResolver;

class GraphQLDataCollector extends DataCollector
{
    /**
     * @var DefinitionRegistry
     */
    protected $registry;

    /**
     * @var EndpointResolver
     */
    protected $endpointResolver;

    /**
     * @var Endpoint
     */
    protected $endpoint;

    /**
     * GraphQLDataCollector constructor.
     *
     * @param DefinitionRegistry $registry
     * @param EndpointResolver   $endpointResolver
     */
    public function __construct(DefinitionRegistry $registry, EndpointResolver $endpointResolver)
    {
        $this->registry = $registry;
        $this->endpointResolver = $endpointResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'graphql';
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
        try {
            $name = $this->endpointResolver->resolveEndpoint($request);
            $this->endpoint = $this->registry->getEndpoint($name);
            $this->data = [
                'endpoint' => $this->endpoint,
            ];
        } catch (EndpointNotValidException $exception) {
            //do nothing
        }
    }

    /**
     * @return Endpoint
     */
    public function getEndpoint()
    {
        return $this->data['endpoint'] ?? null;
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

    /**
     * @param array $data
     *
     * @return Data
     */
    public static function arrayToData(array $data)
    {
        return new Data($data);
    }
}