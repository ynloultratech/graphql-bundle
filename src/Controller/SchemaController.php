<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ynlo\GraphQLBundle\Schema\SchemaExporter;
use Ynlo\GraphQLBundle\Security\EndpointResolver;

class SchemaController
{
    /**
     * @var SchemaExporter
     */
    private $exporter;

    /**
     * @var EndpointResolver
     */
    private $endpointResolver;

    public function __construct(SchemaExporter $exporter, EndpointResolver $endpointResolver)
    {
        $this->exporter = $exporter;
        $this->endpointResolver = $endpointResolver;
    }

    public function __invoke(Request $request): Response
    {
        $name = $this->endpointResolver->resolveEndpoint($request);

        if ('json' === $request->getRequestFormat()) {
            return new JsonResponse($this->exporter->export($name, true));
        }

        return new Response($this->exporter->export($name));
    }
}
