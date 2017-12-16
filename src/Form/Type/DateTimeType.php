<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Ynlo\GraphQLBundle\Type\Types;

/**
 * DateTimeType
 */
class DateTimeType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new CallbackTransformer(
            function ($value) {
            },
            function ($value) {
            }
        );
        $builder->addModelTransformer($transformer);
        $builder->addViewTransformer($transformer);
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('graphql_type', Types::DATETIME);
    }


    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return GraphQLType::class;
    }
}