GraphQLBundle comes with a integrated [Behat](http://behat.org) extension to create test features.

# What is Behat?

Behat is a PHP based framework for Behavioural Driven Development or BDD. 
The simplicity of Behat lies in the fact that it can define 
all possible scenarios and behaviours in simple English steps of when and then. 
This is also known as the [Gherkin language](http://behat.org/en/latest/user_guide/gherkin.html).

# Why Behat?

Behat provide a perfect mix between simplicity and power, look the following example:

````gherkin
Scenario: Get Node
    Given the operation:
    """
    query GetNode($id: ID!){
        node(id: $id) {
            id
            ... on Post {
                title
                body
            }
        }
    }
    """
    And variable "id" is "VXNlcjox"
    When send
    Then the response is OK
    And "{response.data.node.id}" should be equal to "VXNlcjox"
    And "{response.data.node.title}" should be equal to "Welcome"
````    
This example test a GraphQL query to get a node and verify
the content without the need write one single line of "code".

Of course, it's not magic, our extension come with some predefined 
[steps](03_Predefined_Steps.md) 
where the only that you need is change some placeholders, almost magical ;)

> If you’re still new to Behat, take first a look to the 
[Quick Start Behat Guide](http://behat.org/en/latest/quick_start.html)
 then return here and check inside our "Demo" and then use the code 
 that comes with this project to jumpstart testing your API.
 
# Requirements

Must install the following requirements with composer to start using behat tests:

- `behat/behat`: **Required**, is the core of Behat
- `behat/symfony2-extension`: **Required**, integrate behat with symfony
- `phpunit/phpunit`: **Required** The Assert tool is used for assertions
- `symfony/browser-kit`: **Required** The client library to make requests
- `doctrine/doctrine-fixtures-bundle`: **Optional**, if you want to create test fixtures
- `fzaninotto/faker`: **Optional**, if you want to create fake data in your fixtures

Install requirements in one step:

    composer require behat/behat=^3.4 behat/symfony2-extension=^2.1 "phpunit/phpunit=^6.0|^7.0" symfony/browser-kit=^4.0 --dev

Install optionals in one step:

    composer require doctrine/doctrine-fixtures-bundle=^2.4 fzaninotto/faker=^1.7 --dev
    
# Configuration

Create a file called `behat.yml` with the following configuration 
to enable required extensions to start testing your API.

````yaml
default: 
    extensions:
        Ynlo\GraphQLBundle\Behat\GraphQLApiExtension: ~
        Behat\Symfony2Extension: ~
````
> For more details about behat configurations refer
 to the [official documentation](http://behat.org/en/latest/user_guide/configuration.html)

> The [Symfony2Extension](https://github.com/Behat/Symfony2Extension) has some advanced configuration 
to customize how behat will be integrated with symfony.

# Initialization

Execute behat initialization script to create missing things 
to start testing your feature.

    vendor/bin/behat --init
    
At this point you must have a folder called `features` in your project root directory
and a `FeatureContext.php` file inside a bootstrap folder.

````yaml
root
  features
    bootstrap
      FeatureContext.php
````

Update `FeatureContext.php` to extends from `ApiContext`:

````php
<?php

use Ynlo\GraphQLBundle\Behat\Context\ApiContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends ApiContext
{
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }
}
````

Create your first feature inside the `features` folder:
````gherkin
# features/node.feature
Feature: Node
  Scenario: Get Node
    Given the operation:
    """
    query{
      node(id: "VXNlcjox") {
          id
        }
    }
    """
    And send
    Then the response is OK
````

and run:

    vendor/bin/behat
    
That's all, you have created your first feature test.

>> Ensure replace `VXNlcjox` with a real [encoded object id](../08_Reference/01_Object_ID.md)

> This example assume you have a test database configured 
with at least one existent record to fetch. 
Otherwise refer to the [Fixtures](02_Fixtures.md) section to start your tests with
 some fixtures.