<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Definition\Loader;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;
use Ynlo\GraphQLBundle\Annotation\ObjectType;
use Ynlo\GraphQLBundle\Definition\Loader\Annotation\AnnotationParserInterface;
use Ynlo\GraphQLBundle\Definition\Loader\AnnotationLoader;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

class AnnotationLoaderTest extends MockeryTestCase
{
    public function testLoadDefinitions()
    {
        $endpoint = new Endpoint('test');
        $dir = __DIR__.'/../../Fixtures/App';

        $bundle = \Mockery::mock(Bundle::class);
        $bundle->allows('getPath')->times(4)->withNoArgs()->andReturn($dir);
        $bundle->expects('getNamespace')->once()->andReturn('Ynlo\GraphQLBundle\Tests\Fixtures\App');

        $kernel = \Mockery::mock(KernelInterface::class);
        $kernel->expects('getBundles')->andReturn(
            [
                $bundle,
            ]
        );

        $classes = new ArrayCollection();

        $annotation = new ObjectType();
        $reader = \Mockery::mock(Reader::class);
        $reader
            ->expects('getClassAnnotations')
            ->once()
            ->withArgs(
                function (\ReflectionClass $class) use (&$classes) {
                    $classes->add($class);

                    return true;
                }
            )
            ->andReturn([$annotation]);

        $parser = \Mockery::mock(AnnotationParserInterface::class);
        $parser->expects('supports')->once()->with($annotation)->andReturn(true);
        $parser->expects('parse')->withArgs(
            function ($arg1, \ReflectionClass $arg2, Endpoint $arg3) use ($annotation, $classes, $endpoint) {
                return $annotation === $arg1 && $classes->contains($arg2) && $endpoint === $arg3;
            }
        );

        $loader = new AnnotationLoader($kernel, $reader, [$parser]);
        $loader->loadDefinitions($endpoint);

    }
}
