<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Type;

/**
 * Class MutationType
 */
class MutationType extends QueryType
{
    /**
     * MutationType constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $defaults = [
            'name' => 'Mutation',
            'fields' => function () {
                $mutations = [];
                foreach ($this->manager->allMutations() as $query) {
                    $mutations[$query->getName()] = $this->getQueryConfig($query);
                }

                return $mutations;
            },
        ];

        parent::__construct(array_merge($defaults, $config));
    }
}
