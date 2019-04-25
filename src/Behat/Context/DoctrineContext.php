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
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Ynlo\GraphQLBundle\Behat\Client\ClientAwareInterface;
use Ynlo\GraphQLBundle\Behat\Client\ClientAwareTrait;
use Ynlo\GraphQLBundle\Behat\Gherkin\YamlStringNode;
use Ynlo\GraphQLBundle\Behat\Storage\StorageAwareInterface;
use Ynlo\GraphQLBundle\Behat\Storage\StorageAwareTrait;

/**
 * Context for Doctrine integration
 *
 * @property KernelInterface $kernel
 */
final class DoctrineContext implements Context, ClientAwareInterface, StorageAwareInterface
{
    use ClientAwareTrait;
    use StorageAwareTrait;

    /**
     * Use a YAML syntax to populate a repository with multiple records
     *
     * Example: Given the following records in the repository "App\Entity\Post"
     *          """
     *          - title: "Welcome"
     *            body: "Welcome to web page"
     *          - title: "Another Post"
     *            body: "This is another post"
     *          """
     *
     * @Given /^the following records in the repository "([^"]*)"$/
     */
    public function theFollowingRecordsInTheRepository($entity, YamlStringNode $records)
    {
        $manager = $this->getDoctrine()->getManager();
        $accessor = new PropertyAccessor();
        foreach ($records->toArray() as $record) {
            $instance = new $entity();
            foreach ($record as $prop => $value) {
                $accessor->setValue($instance, $prop, $value);
            }

            $manager->persist($instance);
        }
        $manager->flush();
    }

    /**
     * Use a YAML syntax to create a criteria to match a record in given repository
     *
     * Example: Then should exist in repository "AppBundle:Post" a record matching:
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
     * @Given /^should exist in repository "([^"]*)" a record matching:$/
     */
    public function shouldExistInRepositoryARecordMatching($repo, YamlStringNode $criteria)
    {
        $exists = (boolean) $this->getDoctrine()->getRepository($repo)->findOneBy($criteria->toArray());
        Assert::assertTrue($exists, sprintf('Does not exist any record in the repository "%s" matching given conditions', $repo));
    }

    /**
     * Use a YAML syntax to create a criteria to match a record in given repository
     *
     * Example: Then should not exist in repository "AppBundle:Post" a record matching:
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
     * @Given /^should not exist in repository "([^"]*)" a record matching:$/
     */
    public function shouldNotExistInRepositoryARecordMatching($repo, YamlStringNode $criteria)
    {
        $exists = (boolean) $this->getDoctrine()->getRepository($repo)->findOneBy($criteria->toArray());
        Assert::assertFalse($exists, sprintf('Exist at least one record in the repository "%s" matching given conditions', $repo));
    }

    /**
     * Grab a set of records from database in a temp variable inside the `"storage"` in order to use latter in an expression
     *
     * The prefix `"grab in"` is optional and can be used in "Then" for readability
     *
     * <code>
     * Given "users" from ....
     * Then grab in "users" from ...
     * </code>
     *
     * The suffix `"matching:"` is optional too and can be used to set array of conditions to match.
     *
     * ### Placeholders:
     * - **$variable:** `(string)` name of the variable to save in the storage
     * - **$entity:** `(string)` name of the entity using bundle notation `"AppBundle:Entity"` or FQN
     * - **$limitAndOffset:** `(int|string)` amount of records to fetch `"limit"` or use `"limit:offset"` to use pagination
     * - **$orderBy:** `(string|array)` string like `"title:ASC"` or array inside expression like `"{ [{status: 'DESC', title: 'ASC'}] }"`
     * - **$criteria:** `(yaml)` YAML node to convert to array of criteria
     *
     * ### Examples:
     *
     * <code>
     * - Given "orderedUsers" from repository "AppBundle:User" first 5 records ordered by "username:ASC" matching:
     *          """
     *          enabled: true
     *          """
     * - Given "orderedUsers" from repository "AppBundle:User" first 5 records ordered by "username:ASC"
     * - And "orderedUsersWithOffset" from repository "AppBundle:User" first "10:20" records ordered by "username:ASC"
     * - Then grab in "orderedPosts" from repository "AppBundle:Post" first 10 records ordered by "{ [{status: 'DESC', title: 'ASC'}] }"
     * </code>
     *
     * and later can be used:
     *
     * <code>
     * - And "{orderedUsers[0].getUsername()}" should be equal to "{response.data.users.all.edges[0].node.login}"
     * </code>
     *
     * @Given /^(?:grab in )?"([^"]*)" from repository "([^"]*)" first "?([^"]*)"? records ordered by "([^"]*)"(?: matching:)?$/
     */
    public function grabInFromRepositoryFirstRecordsOrderedByMatching($variable, $repo, $limitAndOffset = null, $orderBy = null, YamlStringNode $criteria = null)
    {
        //support to use "limit:offset"
        if (strpos($limitAndOffset, ':') !== false) {
            list($limit, $offset) = explode(':', $limitAndOffset);
        } else {
            $limit = $limitAndOffset;
            $offset = null;
        }
        // support field:ORDER, eg. name:ASC
        if (is_string($orderBy)) {
            list($field, $order) = explode(':', $orderBy);
            $orderBy = [$field => $order];
        }
        $records = $this->getDoctrine()
                        ->getRepository($repo)
                        ->findBy($criteria ? $criteria->toArray() : [], $orderBy, $limit, $offset);
        $this->storage->setValue($variable, $records);
    }

    public function getDoctrine(): Registry
    {
        return $this->client->getContainer()->get('doctrine');
    }

    /**
     * @param string $class
     *
     * @return ObjectRepository|EntityRepository
     */
    public function getRepository(string $class): ObjectRepository
    {
        return $this->getDoctrine()->getRepository($class);
    }
}
