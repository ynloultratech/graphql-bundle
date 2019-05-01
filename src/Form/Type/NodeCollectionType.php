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

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Util\IDEncoder;

/**
 * This form type is helpful when you want to submit sub-collections of nodes inside another node,
 * allow add new items without specific ID and remove items missing in the submitted collection.
 *
 * Example:
 *
 * {
 * "id": "T3JkZXI6MQ==",
 * "number": "ORDER-000001",
 * "items":[
 *      {"id":"SXRlbTox", "title": "Item 1"},
 *      {"title": "Item 2"}
 *    ]
 * }
 *
 * The above example update the first item because the ID is given, add a second item "Item 2" and remove any item
 * currently existent in database bu not submitted.
 *
 * This form type is helpful for example for items inside a Invoice,
 * in this case you don`t need create methods like 'add', 'update' and 'delete' for items,
 * but all items must be submitted on each request. This form type is recommended only
 * for small collection of items.
 *
 * !IMPORTANT: ensure create the methods "add..." and "remove..." like described in https://symfony.com/doc/current/form/form_collections.html#allowing-tags-to-be-removed
 *
 * NOTE: This collection keep the original order of items using the node ID and when data is submitted reorder items
 * again using the submitted ID of every node in order to keep original collection order and
 * avoid the  issue https://github.com/symfony/symfony/issues/4492.
 */
class NodeCollectionType extends CollectionType implements EventSubscriberInterface
{
    protected $orderedItems = [];

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => ['preSubmit', 50],
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder->addEventSubscriber($this);
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $formID = spl_object_hash($event->getForm());
        if (empty($this->orderedItems[$formID]) && $data = $event->getData()) {
            if (is_array($data) || $data instanceof Collection) {
                $this->orderedItems[$formID] = [];
                foreach ($data as $item) {
                    if ($item instanceof NodeInterface) {
                        $this->orderedItems[$formID][$item->getId()] = null;
                    } else {
                        throw new \LogicException('Expect type Node for items');
                    }
                }
            }
        }
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        if ($data = $event->getData()) {
            $formID = spl_object_hash($event->getForm());
            if (is_array($data) && !empty($data)) {
                foreach ($data as $item) {
                    if (isset($item['id'])) {
                        $id = IDEncoder::decode($item['id']);
                        $this->orderedItems[$formID][$id ? $id->getId() : null] = $item;
                    } else {
                        $this->orderedItems[$formID][] = $item;
                    }
                }
            }

            $event->setData(array_filter(array_values($this->orderedItems[$formID])));
        }
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            [
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ]
        );
    }
}
