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

  Scenario: Delete Post
    Given the operation named "DeletePost"
    And variables:
    """
    input:
      clientMutationId: "'{faker.randomNumber}'"
      id: "{#post1}"
    """
    When send
    Then the response is OK
    And "{response.data.posts.delete.clientMutationId}" should be equal to "{variables.input.clientMutationId}"
    And "{response.data.posts.delete.id}" should be equal to "{#post1}"
    And should not exist in repository "AppBundle:Post" a record matching:
    """
    id: "{@post1.getId()}"
    """

  Scenario: Delete Posts (Batch)
    Given the operation named "DeletePosts"
    And variables:
    """
    input:
      clientMutationId: "'{faker.randomNumber}'"
      ids: ["{#post1}", "{#post2}"]
    """
    When send
    Then the response is OK
    And "{response.data.posts.deleteBatch.clientMutationId}" should be equal to "{variables.input.clientMutationId}"
    And "{response.data.posts.deleteBatch.ids}" should be equal to "{variables.input.ids}"
    And should not exist in repository "AppBundle:Post" a record matching:
    """
    id: "{@post1.getId()}"
    """
    And should not exist in repository "AppBundle:Post" a record matching:
    """
    id: "{@post2.getId()}"
    """
