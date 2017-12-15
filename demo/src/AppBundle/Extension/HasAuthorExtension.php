<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\AppBundle\Extension;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Form\FormEvent;
use Ynlo\GraphQLBundle\Demo\AppBundle\Entity\User;
use Ynlo\GraphQLBundle\Demo\AppBundle\Model\HasAuthorInterface;
use Ynlo\GraphQLBundle\Extension\AbstractExtension;

class HasAuthorExtension extends AbstractExtension implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function getPriority(): int
    {
        return 100;
    }

    /**
     * {@inheritDoc}
     */
    public function onSubmit(FormEvent $event)
    {
        if ($event->getData() instanceof HasAuthorInterface) {
            $em = $this->container->get('doctrine')->getManager();
            $user = $em->getRepository(User::class)->findOneBy(['username' => 'admin']);
            $event->getData()->setAuthor($user);
        }
    }
}
