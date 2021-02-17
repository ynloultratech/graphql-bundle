<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use PHPUnit\Framework\Assert;
use Ynlo\GraphQLBundle\Behat\Client\ClientAwareInterface;
use Ynlo\GraphQLBundle\Behat\Client\ClientAwareTrait;
use Ynlo\GraphQLBundle\Behat\Gherkin\YamlStringNode;

/**
 * Context for database integration
 */
final class DatabaseContext implements Context, ClientAwareInterface
{
    use ClientAwareTrait;

    /**
     * Use a YAML syntax to create a criteria to match a record in given table
     *
     * Example: Then should exist in table "post" a record matching:
     *   """
     *   title: "Welcome"
     *   body: "Welcome to web page"
     *   """
     *
     * Expression syntax is allowed
     *
     * Example: Then should exist in table "post" a record matching:
     *   """
     *   title: "{variables.input.title}"
     *   body: "{variables.input.body}"
     *   """
     *
     * @Given /^should exist in table "([^"]*)" a record matching:$/
     */
    public function shouldExistInTableARecordMatching($table, YamlStringNode $criteria)
    {
        $count = $this->countRecordsInTableMatching($table, $criteria->toArray());
        Assert::assertEquals(1, $count, sprintf('Does not exist any record in the database "%s" matching given conditions', $table));
    }

    /**
     * Use a YAML syntax to create a criteria to match many records in given table
     *
     * Example: Then should exist in table "post" a record matching:
     *   """
     *   - title: "Welcome"
     *     body: "Welcome to web page"
     *   - title: "Another Post"
     *     body: "This is another Post"
     *   """
     *
     *
     * @Given /^should exist in table "([^"]*)" records matching:$/
     */
    public function shouldExistInTableRecordsMatching($table, YamlStringNode $criteria)
    {
        foreach ($criteria->toArray() as $row) {
            $count = $this->countRecordsInTableMatching($table, $row);
            Assert::assertEquals(1, $count, sprintf('Does not exist any record in the database "%s" matching given conditions', $table));
        }
    }

    /**
     * Use a YAML syntax to create a criteria to match many records in given table
     *
     * Example: Then should not exist in table "post" a record matching:
     *   """
     *   - title: "Welcome"
     *     body: "Welcome to web page"
     *   - title: "Another Post"
     *     body: "This is another Post"
     *   """
     *
     *
     * @Given /^should not exist in table "([^"]*)" records matching:$/
     */
    public function shouldNotExistInTableRecordsMatching($table, YamlStringNode $criteria)
    {
        foreach ($criteria->toArray() as $row) {
            $count = $this->countRecordsInTableMatching($table, $row);
            Assert::assertEquals(0, $count, sprintf('Exist at least one record in the database "%s" matching given conditions', $table));
        }
    }

    /**
     * Use a YAML syntax to create a criteria to not match a record in given table
     *
     * Example: Then should not exist in table "post" a record matching:
     *   """
     *   title: "Welcome"
     *   body: "Welcome to web page"
     *   """
     *
     * Expression syntax is allowed
     *
     * Example: Then should not exist in table "post" a record matching:
     *   """
     *   title: "{variables.input.title}"
     *   body: "{variables.input.body}"
     *   """
     *
     * @Given /^should not exist in table "([^"]*)" a record matching:$/
     */
    public function shouldNotExistInTableARecordMatching($table, YamlStringNode $criteria)
    {
        $count = $this->countRecordsInTableMatching($table, $criteria->toArray());
        Assert::assertEquals(0, $count, sprintf('Exist at least one record in the database "%s" matching given conditions', $table));
    }

    /**
     * Count records in table matching criteria
     *
     * @param string $table
     * @param array  $criteria
     *
     * @return int
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countRecordsInTableMatching($table, $criteria = []): int
    {
        $where = '';
        foreach ($criteria as $field => $vale) {
            if ($where) {
                $where .= ' AND ';
            }
            if ($vale === null) {
                $where .= sprintf('%s IS NULL', $field);
            } else {
                $where .= sprintf('%s = :%s', $field, $field);
            }
        }
        $query = sprintf('SELECT count(*) AS records FROM %s WHERE %s', $table, $where);

        /** @var EntityManager $manager */
        $manager = $this->client->getContainer()->get('doctrine')->getManager();
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('records', 'records', 'integer');
        $query = $manager->createNativeQuery($query, $rsm);
        $query->setParameters($criteria);

        return (int) $query->getSingleScalarResult();
    }
}
