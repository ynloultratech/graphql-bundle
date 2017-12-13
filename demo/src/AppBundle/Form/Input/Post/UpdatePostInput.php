<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\AppBundle\Form\Input\Post;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * UpdatePostInput
 */
class UpdatePostInput extends AddPostInput
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id');

        parent::buildForm($builder, $options);

        /** @var FormBuilderInterface $field */
        foreach ($builder->all() as $field) {
            $field->setRequired(false);
        }

        $builder->get('id')->setRequired(true);
    }
}
