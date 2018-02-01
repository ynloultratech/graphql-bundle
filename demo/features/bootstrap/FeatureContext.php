<?php

use Ynlo\GraphQLBundle\Behat\Context\ApiContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends ApiContext
{
    use CategoryContextTrait;
}
