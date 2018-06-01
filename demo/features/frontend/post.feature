Feature: Post

  Scenario: Add Post
    Given the operation named "getPostsWithStatus"
    When send
    Then the response is GraphQL error with "You are not authorized to view post status."
