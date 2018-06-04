<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Deprecation;

use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Storage a set of deprecation warnings to display to users after finish all tests
 */
class DeprecationAdviser implements EventSubscriberInterface
{
    /**
     * @var array
     */
    protected $warnings = [];

    public static function getSubscribedEvents()
    {
        return [
            SuiteTested::AFTER => 'dump',
        ];
    }

    /**
     * @param string      $message
     * @param null|string $file
     * @param int|null    $line
     */
    public function addWarning(string $message, ?string $file = null, ?int $line = null)
    {
        $warning = new DeprecationWarning($message, $file, $line);
        $this->warnings[$warning->getMessage()][] = $warning;
    }

    /**
     * Dump all warning ordered by amount of times triggered
     */
    public function dump()
    {
        $message = '';
        if (!empty($this->warnings)) {
            uasort(
                $this->warnings,
                function ($a, $b) {
                    if (count($a) === count($b)) {
                        return 0;
                    }

                    return (count($a) > count($b)) ? -1 : 1;
                }
            );

            foreach ($this->warnings as $message => $warnings) {
                $count = count($warnings);
                echo sprintf("%sx: %s\n", $count, $message);
            }
        }

        return $message;
    }
}
