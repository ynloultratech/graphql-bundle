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
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;
use Ynlo\GraphQLBundle\Type\Registry\TypeRegistry;

/**
 * The snapshot tool can be used to get a fast
 * preview of all schema types and can be used for comparisons
 * with previous schema versions.
 *
 * NOTE: The format exported by this tool is not a official format
 * and can`t be used in any tool out of GraphQLBundle.
 * > Official formats are exported using SchemaExporter
 */
class SchemaSnapshot
{
    protected const SIMPLE_INTROSPECTION_QUERY = <<<GraphQL
query IntrospectionQuery {
    __schema {
      types {
        ...FullType
      }
    }
  }

  fragment FullType on __Type {
    name
    fields(includeDeprecated: true) {
      name
      args {
        ...InputValue
      }
      type {
        ...TypeRef
      }
      isDeprecated
    }
    inputFields {
      ...InputValue
    }
    interfaces {
      ...TypeRef
    }
    enumValues(includeDeprecated: true) {
      name
      isDeprecated
    }
    possibleTypes {
      ...TypeRef
    }
  }

  fragment InputValue on __InputValue {
    name
    type { ...TypeRef }
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
     * @var string
     */
    protected $projectDir;

    /**
     * @var SchemaCompiler
     */
    protected $schemaCompiler;

    /**
     * @var array
     */
    protected $endpoints;

    /**
     * @param SchemaCompiler $schemaCompiler
     */
    public function __construct(SchemaCompiler $schemaCompiler)
    {
        $this->schemaCompiler = $schemaCompiler;
    }

    /**
     * @param string $endpoint
     *
     * @throws \Exception
     *
     * @return array
     */
    public function createSnapshot(string $endpoint = DefinitionRegistry::DEFAULT_ENDPOINT): array
    {
        TypeRegistry::clear();
        $schema = $this->schemaCompiler->compile($endpoint);
        $result = GraphQL::executeQuery($schema, self::SIMPLE_INTROSPECTION_QUERY);

        $schemaArray = $result->toArray();

        //convert the schema in a pretty format for better comparison using diff tools
        $prettySchema = [];
        foreach ($schemaArray['data']['__schema']['types'] as $type) {
            //ignore types starting with __
            if (0 === strpos($type['name'], '__')) {
                continue;
            }

            $prettySchema[$type['name']] = $this->collapseType($type);
        }

        $this->sort($prettySchema);

        return $prettySchema;
    }

    /**
     * @param array $array
     */
    private function sort(&$array): void
    {
        foreach ($array as &$value) {
            if (\is_array($value)) {
                $this->sort($value);
            }
        }
        unset($value);

        ksort($array);
    }

    /**
     * Collapse type definition
     *
     * @param array $originDefinition
     *
     * @return array|null
     */
    private function collapseType(array $originDefinition): ?array
    {
        $definition = [];
        if ($type = $originDefinition['type'] ?? null) {
            $typeName = $type['name'];
            if (!empty($type['ofType'] ?? [])) {
                $typeName = $type['ofType']['name'];
                $ofType = $type;
                if (in_array($ofType['kind'], ['NON_NULL', 'LIST'])) {
                    $typeName = '%s';
                    while ($ofType) {
                        if ($ofType['kind'] === 'NON_NULL') {
                            $typeName = str_replace('%s', '%s!', $typeName);
                        } elseif ($ofType['kind'] === 'LIST') {
                            $typeName = str_replace('%s', '[%s]', $typeName);
                        } else {
                            $typeName = sprintf($typeName, $ofType['name']);
                            break;
                        }
                        $ofType = $ofType['ofType'] ?? null;
                    }
                }
            }

            $definition['type'] = $typeName;
        }

        if ($fields = $originDefinition['fields'] ?? null) {
            foreach ($fields as $field) {
                $definition['fields'][$field['name']] = $this->collapseType($field);
            }
        }

        if ($inputFields = $originDefinition['inputFields'] ?? null) {
            foreach ($inputFields as $inputField) {
                $definition['inputFields'][$inputField['name']] = $this->collapseType($inputField);
            }
        }

        if ($args = $originDefinition['args'] ?? null) {
            foreach ($args as $arg) {
                $definition['args'][$arg['name']] = $this->collapseType($arg);
            }
        }

        if ($possibleTypes = $originDefinition['possibleTypes'] ?? null) {
            foreach ($possibleTypes as $possibleType) {
                $definition['possibleTypes'][$possibleType['name']] = $this->collapseType($possibleType);
            }
        }

        if ($interfaces = $originDefinition['interfaces'] ?? null) {
            foreach ($interfaces as $interface) {
                $definition['interfaces'][] = $interface['name'];
            }
        }

        if ($enumValues = $originDefinition['enumValues'] ?? null) {
            foreach ($enumValues as $enumValue) {
                $definition['enumValues'][] = $enumValue['name'];
            }
        }

        return empty($definition) ? null : $definition;
    }
}
