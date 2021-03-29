<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Behat\Transformer;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Definition\Definition;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;
use Faker\Factory;
use Mockery;
use Ynlo\GraphQLBundle\Behat\Storage\Storage;
use Ynlo\GraphQLBundle\Behat\Transformer\ExpressionLanguage\FakerProvider;
use Ynlo\GraphQLBundle\Behat\Transformer\ExpressionLanguage\JMESPathSearchProvider;
use Ynlo\GraphQLBundle\Behat\Transformer\ExpressionLanguage\StorageValuesProvider;
use Ynlo\GraphQLBundle\Behat\Transformer\TransformStringToExpression;

class TransformStringToExpressionTest extends Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @var TransformStringToExpression
     */
    protected $transformer;

    /**
     * @var DefinitionCall
     */
    protected $definitionCall;

    /**
     * @var Mockery\Mock
     */
    protected $env;

    /**
     * @var Mockery\Mock
     */
    protected $feature;

    /**
     * @var Mockery\Mock
     */
    protected $step;

    /**
     * @var Mockery\Mock
     */
    protected $definition;

    public function setUp(): void
    {
        $this->transformer = new TransformStringToExpression();

        $this->env = Mockery::mock(Environment::class);
        $this->feature = Mockery::mock(FeatureNode::class);
        $this->step = Mockery::mock(StepNode::class);
        $this->definition = Mockery::mock(Definition::class);
        $this->definitionCall = new DefinitionCall($this->env, $this->feature, $this->step, $this->definition, []);
    }

    public function testSupportsDefinitionAndArgument()
    {
        self::assertTrue($this->transformer->supportsDefinitionAndArgument($this->definitionCall, 0, '{someValue}'));
        self::assertTrue($this->transformer->supportsDefinitionAndArgument($this->definitionCall, 0, '{some.value[]}'));
        self::assertFalse($this->transformer->supportsDefinitionAndArgument($this->definitionCall, 0, 'some.value[]'));
        self::assertFalse($this->transformer->supportsDefinitionAndArgument($this->definitionCall, 0, '"{some.value[]}"'));
    }

    public function testTransformArgument()
    {
        $faker = Factory::create();

        $value = $this->transformer->transformArgument($this->definitionCall, 0, '{2+1}');
        self::assertEquals(3, $value);

        $this->transformer->registerPreprocessor(new FakerProvider());
        $value = $this->transformer->transformArgument($this->definitionCall, 0, '{faker.sentence}');
        self::assertNotNull($value);

        $data = [
            'posts' => [
                ['title' => $faker->sentence, 'published' => 'true'],
                ['title' => $faker->sentence, 'published' => 'false'],
                ['title' => $faker->sentence, 'published' => 'true'],
                ['title' => $faker->sentence, 'published' => 'false'],
                ['title' => $faker->sentence, 'published' => 'false'],
            ],
        ];

        $this->transformer->registerPreprocessor(new StorageValuesProvider(new Storage($data)));
        $this->transformer->registerPreprocessor(new JMESPathSearchProvider());
        $value = $this->transformer->transformArgument($this->definitionCall, 0, "{ search('[0].title', posts) }");
        self::assertEquals($data['posts'][0]['title'], $value);

        $value = $this->transformer->transformArgument($this->definitionCall, 0, "{ search('[*].title', posts) }");
        self::assertEquals(
            [
                $data['posts'][0]['title'],
                $data['posts'][1]['title'],
                $data['posts'][2]['title'],
                $data['posts'][3]['title'],
                $data['posts'][4]['title'],
            ],
            $value
        );

        $value = $this->transformer->transformArgument($this->definitionCall, 0, "{ search('[?published==\'true\'].title', posts) }");
        self::assertEquals(
            [
                $data['posts'][0]['title'],
                $data['posts'][2]['title'],
            ],
            $value
        );

        $value = $this->transformer->transformArgument($this->definitionCall, 0, "{ search('[:3].title', posts) }");
        self::assertEquals(
            [
                $data['posts'][0]['title'],
                $data['posts'][1]['title'],
                $data['posts'][2]['title'],
            ],
            $value
        );
    }
}
