Your API schema change during the lifecycle of your project, 
but is very important keep your API functional during this changes.
GraphQL [does not need a version system like REST does](https://graphql.org/learn/best-practices/#versioning).

The best way to keep backward compatibility while your system grows
is verifying that your schema is the expected. GraphQLBundles comes
with a test feature and command to help you with this task.

>> To use Snapshots must have Behat tests configured and working.

Execute the following command:

`bin/console graphql:schema:snapshot --all`

The above command generate a snapshot for each registered endpoint and create
a behat feature called `snapshot.feature` inside `features` folder in your project root.

The `snapshot.feature` looks like this:

````
Feature: Schema Snapshot

  Scenario: Verify "default" Endpoint
    Given previous schema snapshot of "default" endpoint
    When compare with current schema
    Then current schema is compatible with latest snapshot
````

Now every time you remove or change some field this test fail because your schema is not compatible
with the latest snapshot. In the other hand, additions are not taken into account
and all tests will continue to function normally.

If you want verify your schema entirely, including additions and minor changes use `strict` mode
when generate the feature file.

`bin/console graphql:schema:snapshot --strict --all`


````
Feature: Schema Snapshot

  Scenario: Verify "default" Endpoint
    Given previous schema snapshot of "default" endpoint
    When compare with current schema
    Then current schema is compatible with latest snapshot
    And current schema is same after latest snapshot
````

> Strict verification is useful when you have multiple endpoints to control which definitions are published at each point. 
This helps avoid accidentally exporting an operation to an unwanted endpoint.

# Update Snapshot

After some change in your schema must execute `graphql:schema:snapshot --all` in order to update
your snapshots with these changes. If you are using a VCS like git, then is very easy review all your 
changes on each snapshots and commit the file if all is ok. 

The `graphql:schema:snapshot` command has other options, for example `--endpoint=[name]` in order to
update only one endpoint. To view all command options execute `bin/console graphql:schema:snapshot --help`
 
