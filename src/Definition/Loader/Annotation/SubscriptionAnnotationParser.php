<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Loader\Annotation;

use Ynlo\GraphQLBundle\Annotation;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Definition\SubscriptionDefinition;
use Ynlo\GraphQLBundle\Definition\UnionDefinition;
use Ynlo\GraphQLBundle\Definition\UnionTypeDefinition;
use Ynlo\GraphQLBundle\Subscription\SubscriptionLink;
use Ynlo\GraphQLBundle\Util\ClassUtils;
use Ynlo\GraphQLBundle\Util\TypeUtil;

/**
 * Parse subscription annotation to fetch definitions
 */
class SubscriptionAnnotationParser extends QueryAnnotationParser
{
    /**
     * {@inheritdoc}
     */
    public function supports($annotation): bool
    {
        return $annotation instanceof Annotation\Subscription;
    }

    /**
     * {@inheritdoc}
     *
     * @param Annotation\Subscription $annotation
     */
    public function parse($annotation, \ReflectionClass $refClass, Endpoint $endpoint)
    {
        /** @var Annotation\Subscription $annotation */

        if (!preg_match('/\\Subscription\\\\/', $refClass->getName())) {
            $error = sprintf(
                'Annotation "@Subscription" in the class "%s" is not valid, 
            mutations can only be applied to classes inside "...Bundle\Subscription\..."',
                $refClass->getName()
            );
            throw new \RuntimeException($error);
        }

        if (!$annotation->resolver && !$refClass->hasMethod('__invoke')) {
            $error = sprintf(
                'The class "%s" should have a method "__invoke" to process the subscription.',
                $refClass->getName()
            );
            throw new \RuntimeException($error);
        }

        $subscription = new SubscriptionDefinition();
        $subscription->setType(TypeUtil::normalize($annotation->payload, $endpoint));

        if ($annotation->name) {
            $subscription->setName($annotation->name);
        } else {
            $subscription->setName(lcfirst(ClassUtils::getDefaultName($refClass->getName())));
        }

        if (!$subscription->getType()) {
            $nodeType = ClassUtils::getNodeFromClass($refClass->getName());
            $objectDefinition = null;
            if ($nodeType && $endpoint->hasType($nodeType)) {
                $objectDefinition = $endpoint->getType($nodeType);
            }
            if ($objectDefinition) {
                $subscription->setType($objectDefinition->getName());
                $subscription->setNode($objectDefinition->getName());
            } else {
                $error = sprintf('Does not exist any valid type for class "%s"', $refClass->getName());
                throw new \RuntimeException($error);
            }
        }

        $endpoint->addSubscription($subscription);

        if (!$annotation->payload) {
            if (class_exists($refClass->getName().'Payload')) {
                $annotation->payload = $refClass->getName().'Payload';
                if (!$endpoint->hasTypeForClass($annotation->payload)) {
                    $error = sprintf(
                        'The payload "%s" exist but does not exist a valid GraphQL type, is missing ObjectType annotation?',
                        $annotation->payload
                    );
                    throw new \RuntimeException($error);
                }
            }
        }

        // create subscription event
        $subscriptionEvent = new ObjectDefinition();
        $subscriptionEvent->setName(ucfirst($subscription->getName()).'Event');
        $field = new FieldDefinition();
        $field->setName($annotation->fieldName ?? 'data');
        $field->setDescription($annotation->fieldDescription ?? null);
        $field->setType($subscription->getType());
        $field->setNode($subscription->getNode());
        $field->setNonNull(TypeUtil::isTypeNonNull($annotation->payload));
        $field->setList(TypeUtil::isTypeList($annotation->payload));
        $field->setNonNullList(TypeUtil::isTypeNonNullList($annotation->payload));
        $subscriptionEvent->addField($field);
        $endpoint->addType($subscriptionEvent);

        // subscription response is a union between SubscriptionLink object and specific Subscription Event
        $subscriptionResponse = new UnionDefinition();
        $subscriptionResponse->setName(ucfirst($subscription->getName()).'Subscription');
        $subscriptionResponse->addType(new UnionTypeDefinition($subscriptionEvent->getName()));
        $subscriptionResponse->addType(new UnionTypeDefinition($endpoint->getTypeForClass(SubscriptionLink::class)));
        $endpoint->addType($subscriptionResponse);
        $subscription->setType(TypeUtil::normalize($subscriptionResponse->getName(), $endpoint));

        if (!$subscription->getType()) {
            $error = sprintf(
                'The subscription "%s" does not have a valid payload,
                 create a file called %sPayload or specify a payload.',
                $subscription->getName(),
                $refClass->getName()
            );
            throw new \RuntimeException($error);
        }

        $argAnnotations = $this->reader->getClassAnnotations($refClass);
        foreach ($argAnnotations as $argAnnotation) {
            if ($argAnnotation instanceof Annotation\Argument) {
                $this->resolveArgument($subscription, $argAnnotation);
            }
        }

        if ($annotation->node) {
            $subscription->setNode($annotation->node);
        } elseif (($node = ClassUtils::getNodeFromClass($refClass->getName())) && $endpoint->hasType($node)) {
            $subscription->setNode($node);
        }

        $subscription->setResolver($annotation->resolver ?? $refClass->getName());
        $subscription->setDeprecationReason($annotation->deprecationReason);
        $subscription->setDescription($annotation->description);

        foreach ($annotation->options as $option => $value) {
            $subscription->setMeta($option, $value);
        }
    }
}
