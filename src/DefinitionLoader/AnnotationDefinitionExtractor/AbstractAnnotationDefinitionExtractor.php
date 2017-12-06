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

/**
 * Class AbstractAnnotationDefinitionExtractor
 */
abstract class AbstractAnnotationDefinitionExtractor implements AnnotationDefinitionExtractorInterface
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * {@inheritdoc}
     */
    public function setReader(Reader $reader)
    {
        $this->reader = $reader;
    }
}
