<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Schema;

use GraphQL\GraphQL;
use GraphQL\Utils\SchemaPrinter;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

class SchemaExporter
{
    /**
     * @var SchemaCompiler
     */
    protected $compiler;

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

    /**
     * @param SchemaCompiler $compiler
     */
    public function __construct(SchemaCompiler $compiler)
    {
        $this->compiler = $compiler;
    }

    /**
     * @param Endpoint $endpoint
     * @param bool     $json
     *
     * @return string
     */
    public function export(Endpoint $endpoint, bool $json = false): string
    {
        $schema = $this->compiler->compile($endpoint);

        if ($json) {
            $result = GraphQL::executeQuery($schema, self::INTROSPECTION_QUERY);

            return json_encode($result->toArray(), JSON_PRETTY_PRINT);
        }

        return SchemaPrinter::doPrint($schema);
    }
}
