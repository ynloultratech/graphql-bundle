<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Loader\Annotation;

use Ynlo\GraphQLBundle\Definition\Registry\DefinitionManager;

/**
 * Interface AnnotationParserInterface
 */
interface AnnotationParserInterface
{
    /**
     * @param mixed $annotation
     *
     * @return bool
     */
    public function supports($annotation): bool;

    /**
     * @param mixed             $annotation
     * @param \ReflectionClass  $refClass
     * @param DefinitionManager $definitionManager
     */
    public function parse($annotation, \ReflectionClass $refClass, DefinitionManager $definitionManager);
}
