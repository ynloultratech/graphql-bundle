<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Form\Input\Profile;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Ynlo\GraphQLBundle\Form\Type\DateTimeType;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Profile;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Form\Input\Photo\PhotoInput;

class ProfileInput extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nick');
        $builder->add('firstName');
        $builder->add('lastName');
        $builder->add('single', CheckboxType::class);
        $builder->add('credits', NumberType::class);
        $builder->add('reputation', IntegerType::class);
        $builder->add('birthDate', DateTimeType::class);
        $builder->add('hobbies', CollectionType::class);
        $builder->add(
            'photos',
            CollectionType::class,
            [
                'entry_type' => PhotoInput::class,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(
            [
                'data_class' => Profile::class,
            ]
        );
    }
}
