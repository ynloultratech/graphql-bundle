<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Resolver;

use Doctrine\Common\Collections\Collection;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Model\ID;
use Ynlo\GraphQLBundle\Type\EndpointAwareInterface;
use Ynlo\GraphQLBundle\Type\EndpointAwareTrait;

/**
 * Default resolver for all object fields
 */
class ObjectFieldResolver implements ContainerAwareInterface, EndpointAwareInterface
{
    use ContainerAwareTrait;
    use EndpointAwareTrait;

    /**
     * @var FieldsAwareDefinitionInterface
     */
    protected $definition;

    /**
     * ObjectFieldResolver constructor.
     *
     * @param ContainerInterface             $container
     * @param Endpoint                       $endpoint
     * @param FieldsAwareDefinitionInterface $definition
     */
    public function __construct(ContainerInterface $container, Endpoint $endpoint, FieldsAwareDefinitionInterface $definition)
    {
        $this->definition = $definition;
        $this->container = $container;
        $this->endpoint = $endpoint;
    }

    /**
     * @param mixed       $root
     * @param array       $args
     * @param mixed       $context
     * @param ResolveInfo $info
     *
     * @return mixed|null|string
     */
    public function __invoke($root, array $args, $context, ResolveInfo $info)
    {
        $value = null;
        $fieldDefinition = $this->definition->getField($info->fieldName);

        //when use external resolver or use a object method with arguments
        if ($fieldDefinition->getResolver() || $fieldDefinition->getArguments()) {
            $queryDefinition = new QueryDefinition();
            $queryDefinition->setName($fieldDefinition->getName());
            $queryDefinition->setType($fieldDefinition->getType());
            $queryDefinition->setArguments($fieldDefinition->getArguments());
            $queryDefinition->setList($fieldDefinition->isList());
            $queryDefinition->setMetas($fieldDefinition->getMetas());

            if (!$fieldDefinition->getResolver()) {
                if ($fieldDefinition->getOriginType() === \ReflectionMethod::class) {
                    $queryDefinition->setResolver($fieldDefinition->getOriginName());
                }
            } else {
                $queryDefinition->setResolver($fieldDefinition->getResolver());
            }

            $resolver = new ResolverExecutor($this->container, $this->endpoint, $queryDefinition);
            $value = $resolver($root, $args, $context, $info);
        } else {
            $accessor = new PropertyAccessor(true);
            $originName = $fieldDefinition->getOriginName() ?? $fieldDefinition->getName();
            $value = $accessor->getValue($root, $originName);
        }

        if (null !== $value && Type::ID === $fieldDefinition->getType()) {
            //ID are formed with base64 representation of the Types and real database ID
            //in order to create a unique and global identifier for each resource
            //@see https://facebook.github.io/relay/docs/graphql-object-identification.html
            if ($value instanceof ID) {
                $value = (string) $value;
            } else {
                $value = (string) new ID($this->definition->getName(), $value);
            }
        }

        if ($value instanceof Collection) {
            $value = $value->toArray();
        }

        return $value;
    }
}
