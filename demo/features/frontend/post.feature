Feature: Post

  Scenario: Deny user get post status field
    Given the operation named "getPostsWithStatus"
    When send
    Then the response is GraphQL error with "You are not authorized to view post status."

  Scenario: Deny user add Post
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
    Then the response is GraphQL error with "Does not have enough permissions tu publish new posts."

