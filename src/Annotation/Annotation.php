<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Annotation;

/**
 * Class Annotation
 */
abstract class Annotation
{
    /**
     * @param array $data Key-value for properties to be defined in this class.
     */
    public function __construct(array $data)
    {
        if (count($data) > 1 || !isset($data['value'])) {
            foreach ($data as $key => $value) {
                $this->$key = $value;
            }
        } else {
            if (isset($data['value'])) {
                $ref = new \ReflectionClass(\get_class($this));
                $props = $ref->getProperties(T_PUBLIC);
                $props[0]->setValue($this, $data['value']);
            }
        }
    }
}
