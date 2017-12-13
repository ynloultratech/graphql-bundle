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
        $transformer = new IDToNodeTransformer($this->em, $this->defRegistry->getEndpoint());
        $builder->addModelTransformer($transformer);
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('multiple', false);
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return TextType::class;
    }
}
