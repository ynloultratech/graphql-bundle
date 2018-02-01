<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Gherkin;

use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\Yaml\Yaml;

/**
 * Represents Gherkin PyString argument with YAML support
 */
class YamlStringNode extends PyStringNode
{
    /**
     * @var []
     */
    protected $array;

    /**
     * Initializes PyString.
     *
     * @param array   $strings String in form of [$stringLine]
     * @param integer $line    Line number where string been started
     */
    public function __construct(array $strings, $line)
    {
        parent::__construct($strings, $line);

        $this->array = Yaml::parse($this->getRaw());
    }

    /**
     * @return mixed
     */
    public function toArray()
    {
        return $this->array;
    }

    /**
     * @param mixed $array
     */
    public function setArray($array): void
    {
        $this->array = $array;
    }
}
