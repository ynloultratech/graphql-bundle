Feature: Post

  Scenario: Add Post
    Given the operation named "AddPost"
    And variables:
    """
    input:
      clientMutationId: "'{faker.randomNumber}'"
      status: PUBLISHED
      title: "{faker.sentence}"
      body: "{faker.paragraph}"
      tags: ['asd', 'asdsd']
      categories:
        - "#category1"
        - "#category2"
    """
    When send
    Then the response is OK
    And "{response.data.posts.add.clientMutationId}" should be equal to "{variables.input.clientMutationId}"
    And "{response.data.posts.add.node.title}" should be equal to "{variables.input.title}"
    And "{response.data.posts.add.node.body}" should be equal to "{variables.input.body}"
    And "{response.data.posts.add.node.status}" should be equal to "{variables.input.status}"
    And "{response.data.posts.add.node.tags}" should be equal to "{variables.input.tags}"
    And "{response.data.posts.add.node.categories[0].name}" should be equal to "{@category1.getName()}"
    And "{response.data.posts.add.node.categories[1].name}" should be equal to "{@category2.getName()}"
    And should exist in repository "AppBundle:Post" a record matching:
      """
      title: "{response.data.posts.add.node.title}"
      body: "{response.data.posts.add.node.body}"
      """

  Scenario: Add Post with a Future Date
    Given the operation named "AddPost"
    And variables:
    """
    input:
      clientMutationId: "'{faker.randomNumber}'"
      status: FUTURE
      futurePublishDate: "1985-06-18T18:05:00-05:00"
      title: "{faker.sentence}"
      body: "{faker.paragraph}"
      categories: ["#category1"]
    """
    When send
    Then the response is OK
    And "{response.data.posts.add.clientMutationId}" should be equal to "{variables.input.clientMutationId}"
    And "{response.data.posts.add.node.title}" should be equal to "{variables.input.title}"
    And "{response.data.posts.add.node.status}" should be equal to "{variables.input.status}"
    And "{response.data.posts.add.node.futurePublishDate}" should be equal to "{variables.input.futurePublishDate}"
