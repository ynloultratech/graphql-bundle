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

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;
use Ynlo\GraphQLBundle\Form\DataTransformer\IDToNodeTransformer;

/**
 * Class IDType
 */
class IDType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var DefinitionRegistry
     */
    protected $defRegistry;

    /**
     * IDToNodeTransformer constructor.
     *
     * @param EntityManagerInterface $em
     * @param DefinitionRegistry     $defRegistry
     */
    public function __construct(EntityManagerInterface $em, DefinitionRegistry $defRegistry)
    {
        $this->em = $em;
        $this->defRegistry = $defRegistry;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $repo = null;
        $findBy = null;
        if ($builder->getOption('class') && $builder->getOption('alternative_id')) {
            $repo = $this->em->getRepository($builder->getOption('class'));
            $findBy = $builder->getOption('alternative_id');
        }
        $transformer = new IDToNodeTransformer($repo, $findBy);
        $builder->addModelTransformer($transformer);
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('multiple', false);

        // The following option allow set custom alternative columns to match given value with a node
        // By default API consumers must need set the "id" (WVhOayUXhNZz09) to match a object
        // with this configuration can use another alternative column or columns to find a node.
        //
        // For example:
        //
        // $builder->add('product', IDType::class, ['alternative_id' => 'sku']);
        //
        // Now the form input can accept: "product": 1234 or "product": "WVhOayUXhNZz09"
        //
        $resolver->setDefault('class', null);
        $resolver->setDefault('alternative_id', null);
        $resolver->setNormalizer(
            'alternative_id',
            function (Options $options, $value) {
                if ($value && !$options->offsetGet('class')) {
                    throw new InvalidArgumentException('Can\'t set the option "alternative_id" without define a valid "class"');
                }

                return $value;
            }
        );

        $resolver->setAllowedTypes('class', ['string', 'null']);
        $resolver->setAllowedTypes('alternative_id', ['string', 'array', 'null']);
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return TextType::class;
    }
}
