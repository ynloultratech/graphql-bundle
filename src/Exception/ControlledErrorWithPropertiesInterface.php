<?php

namespace Ynlo\GraphQLBundle\Exception;

interface ControlledErrorWithPropertiesInterface
{
    /**
     * Array of indexed properties
     *
     * @return array
     */
    public function getProperties(): array;
}