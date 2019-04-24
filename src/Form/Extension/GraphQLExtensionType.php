<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * GraphQLExtensionType
 */
class GraphQLExtensionType extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('graphql_type', null);
        $resolver->setDefault('graphql_description', null);
        $resolver->setDefault('graphql_deprecation_reason', null);
        $resolver->setDefault('endpoint', null);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        // support BC with symfony <4.1
        return self::getExtendedTypes()[0];
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes()
    {
        return [FormType::class];
    }
}
