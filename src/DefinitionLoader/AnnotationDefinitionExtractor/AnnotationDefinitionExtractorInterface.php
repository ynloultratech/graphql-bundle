<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\DefinitionLoader\AnnotationDefinitionExtractor;

use Doctrine\Common\Annotations\Reader;
use Ynlo\GraphQLBundle\DefinitionLoader\DefinitionManager;

/**
 * Interface AnnotationDefinitionExtractorInterface
 */
interface AnnotationDefinitionExtractorInterface
{
    /**
     * @param Reader $reader
     */
    public function setReader(Reader $reader);

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
    public function extract($annotation, \ReflectionClass $refClass, DefinitionManager $definitionManager);
}
