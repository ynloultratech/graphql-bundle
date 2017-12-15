<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\AppBundle\Form\Input\Comment;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Ynlo\GraphQLBundle\Form\Type\IDType;

/**
 * AddCommentInput
 */
class AddCommentInput extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'commentable',
                IDType::class,
                [
                    'constraints' => [new NotBlank()],
                ]
            )
            ->add(
                'body',
                null,
                [
                    'constraints' => [new NotBlank()],
                ]
            );
    }
}
