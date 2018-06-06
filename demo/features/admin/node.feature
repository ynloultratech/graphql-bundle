@user:admin
Feature: Read Nodes

  Scenario: Get Node
    Given the operation named "GetNode"
    And variable "id" is "#post1"
    When send
    Then the response is OK
    And "{response.data.node.id}" should be equal to "#post1"
    And "{response.data.node.title}" should be equal to "{@post1.getTitle()}"
    And "{response.data.node.body}" should be equal to "{@post1.getBody()}"

  Scenario: Get Nodes
    Given the operation named "GetNodes"
    And variable "ids" is "{[#post1, #post2, #post3]}"
    When send
    Then the response is OK
    And "{response.data.nodes[0].id}" should be equal to "#post1"
    And "{response.data.nodes[0].title}" should be equal to "{@post1.getTitle()}"
    And "{response.data.nodes[1].id}" should be equal to "#post2"
    And "{response.data.nodes[1].title}" should be equal to "{@post2.getTitle()}"
    And "{response.data.nodes[2].id}" should be equal to "#post3"
    And "{response.data.nodes[2].title}" should be equal to "{@post3.getTitle()}"

  Scenario: Get Nodes in Specific Order
    Given the operation named "GetNodes"
    And variable "ids" is "{[#post2, #post3, #post1]}"
    When send
    Then the response is OK
    And "{response.data.nodes[0].id}" should be equal to "#post2"
    And "{response.data.nodes[0].title}" should be equal to "{@post2.getTitle()}"
    And "{response.data.nodes[1].id}" should be equal to "#post3"
    And "{response.data.nodes[1].title}" should be equal to "{@post3.getTitle()}"
    And "{response.data.nodes[2].id}" should be equal to "#post1"
    And "{response.data.nodes[2].title}" should be equal to "{@post1.getTitle()}"
