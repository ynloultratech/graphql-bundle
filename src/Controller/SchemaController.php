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

use GraphQL\GraphQL;
use GraphQL\Utils\SchemaPrinter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ynlo\GraphQLBundle\Schema\SchemaCompiler;
use Ynlo\GraphQLBundle\Security\EndpointResolver;

class SchemaController
{
    /**
     * @var SchemaCompiler
     */
    private $compiler;

    /**
     * @var EndpointResolver
     */
    private $endpointResolver;

    private const INTROSPECTION_QUERY = <<<GraphQL
query IntrospectionQuery {
    __schema {
      queryType { name }
      mutationType { name }
      subscriptionType { name }
      types {
        ...FullType
      }
      directives {
        name
        description
        locations
        args {
          ...InputValue
        }
      }
    }
  }

  fragment FullType on __Type {
    kind
    name
    description
    fields(includeDeprecated: true) {
      name
      description
      args {
        ...InputValue
      }
      type {
        ...TypeRef
      }
      isDeprecated
      deprecationReason
    }
    inputFields {
      ...InputValue
    }
    interfaces {
      ...TypeRef
    }
    enumValues(includeDeprecated: true) {
      name
      description
      isDeprecated
      deprecationReason
    }
    possibleTypes {
      ...TypeRef
    }
  }

  fragment InputValue on __InputValue {
    name
    description
    type { ...TypeRef }
    defaultValue
  }

  fragment TypeRef on __Type {
    kind
    name
    ofType {
      kind
      name
      ofType {
        kind
        name
        ofType {
          kind
          name
          ofType {
            kind
            name
            ofType {
              kind
              name
              ofType {
                kind
                name
                ofType {
                  kind
                  name
                }
              }
            }
          }
        }
      }
    }
  }
GraphQL;


    public function __construct(SchemaCompiler $compiler, EndpointResolver $endpointResolver)
    {
        $this->compiler = $compiler;
        $this->endpointResolver = $endpointResolver;
    }

    public function __invoke(Request $request): Response
    {
        $name = $this->endpointResolver->resolveEndpoint($request);
        $schema = $this->compiler->compile($name);

        if ('json' === $request->getRequestFormat()) {
            $result = GraphQL::executeQuery($schema, self::INTROSPECTION_QUERY);

            return JsonResponse::create($result->toArray());
        }

        return new Response(SchemaPrinter::doPrint($schema));
    }
}
