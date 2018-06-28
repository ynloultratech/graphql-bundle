<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests;

use Ynlo\GraphQLBundle\Definition\Loader\Annotation\AnnotationParserInterface;
use Ynlo\GraphQLBundle\Definition\Loader\Annotation\FieldDecorator\DoctrineFieldDefinitionDecorator;
use Ynlo\GraphQLBundle\Definition\Loader\Annotation\FieldDecorator\GraphQLFieldDefinitionDecorator;
use Ynlo\GraphQLBundle\Definition\Loader\Annotation\MutationAddUpdateAnnotationParser;
use Ynlo\GraphQLBundle\Definition\Loader\Annotation\MutationAnnotationParser;
use Ynlo\GraphQLBundle\Definition\Loader\Annotation\MutationDeleteAnnotationParser;
use Ynlo\GraphQLBundle\Definition\Loader\Annotation\MutationDeleteBatchAnnotationParser;
use Ynlo\GraphQLBundle\Definition\Loader\Annotation\ObjectTypeAnnotationParser;
use Ynlo\GraphQLBundle\Definition\Loader\Annotation\QueryAnnotationParser;
use Ynlo\GraphQLBundle\Definition\Loader\Annotation\QueryListAnnotationParser;
use Ynlo\GraphQLBundle\Definition\Loader\Annotation\StandaloneFieldParser;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

/**
 * Helper to load definitions and populate endpoint in order to make tests
 */
class TestDefinitionHelper
{
    /**
     * Load annotations definitions for given class in given endpoint
     *
     * @param string   $class    object to load definitions
     * @param Endpoint $endpoint endpoint to load definitions
     * @param array    $toLoad   Array of annotations to load, leave empty to load all
     */
    public static function loadAnnotationDefinitions($class, Endpoint $endpoint, array $toLoad = []): void
    {
        try {
            $ref = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            $ref = null;
        }
        $reader = TestAnnotationReader::create();
        $annotations = $reader->getClassAnnotations($ref);


        $fieldDecorators = [
            new DoctrineFieldDefinitionDecorator($reader),
            new GraphQLFieldDefinitionDecorator($reader),
        ];

        $parsers = [
            new ObjectTypeAnnotationParser($reader, $fieldDecorators),
            new QueryAnnotationParser($reader),
            new QueryListAnnotationParser($reader),
            new MutationAnnotationParser($reader),
            new MutationAddUpdateAnnotationParser($reader),
            new MutationDeleteAnnotationParser($reader),
            new MutationDeleteBatchAnnotationParser($reader),
            new StandaloneFieldParser($reader),
        ];

        /** @var AnnotationParserInterface $parser */
        foreach ($parsers as $parser) {
            foreach ($annotations as $annotation) {
                if ($toLoad && !\in_array(get_class($annotation), $toLoad)) {
                    continue;
                }
                if ($parser->supports($annotation)) {
                    $parser->parse($annotation, $ref, $endpoint);
                }
            }
        }
    }
}
