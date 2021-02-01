<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Transformer;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Transformation\Transformer\ArgumentTransformer;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\Yaml\Yaml;
use Ynlo\GraphQLBundle\Behat\Gherkin\YamlStringNode;

/**
 * Transform a pyString node into a YAMLStringNode
 * Must use YAMLStringNode has typehint in the method
 * and use the step as normal pystring
 *
 * @example setArray(YamlStringNode $variables){
 *              $variables->toArray();
 *              //...
 *         }
 *
 *          And set array:
 *          """
 *          - var1
 *          - var2
 *          - var3
 *          """
 */
class TransformPystringToYamlstring implements ArgumentTransformer
{
    /**
     * @var ArgumentTransformer[]
     */
    protected $transformers;

    public function __construct($transformers)
    {
        $this->transformers = $transformers;
    }

    public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        $refMethod = $definitionCall->getCallee()->getReflection();
        $refParam = $refMethod->getParameters()[$argumentIndex] ?? null;

        if ($argumentValue instanceof PyStringNode && $refParam && $type = $refParam->getType()) {
            return is_a($type->getName(), YamlStringNode::class, true);
        }
    }

    public function transformArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        /** @var PyStringNode $argumentValue */
        $parsedValue = Yaml::parse($argumentValue);

        //parse
        array_walk_recursive(
            $parsedValue,
            function (&$value) use ($definitionCall, $argumentIndex) {
                foreach ($this->transformers as $transformer) {
                    if ($transformer->supportsDefinitionAndArgument($definitionCall, $argumentIndex, $value)) {
                        $value = $transformer->transformArgument($definitionCall, $argumentIndex, $value);
                    }
                }
            }
        );
        $node = new YamlStringNode($argumentValue->getStrings(), $argumentValue->getLine());
        $node->setArray($parsedValue);

        return $node;
    }
}
