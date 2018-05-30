<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\AppBundle\Form\Input\User;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class UpdateUserInput
 */
class UpdateUserInput extends AddUserInput
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id');
        parent::buildForm($builder, $options);

        $builder->get('login')->setRequired(false);
        $builder->get('email')->setRequired(false);
        $builder->get('password')->setRequired(false);
        $builder->get('profile')->setRequired(false);
    }
}
