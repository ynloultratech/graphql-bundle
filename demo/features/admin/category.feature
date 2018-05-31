@admin
Feature: Category

  Scenario: List Categories with posts
    Given the operation named "ListCategoriesWithPosts"
    And variables:
    """
    firstCategories: 3
    firstPosts: 2
    orderPostsBy: title
    """
    When send
    Then the response is OK
    And "{response.data.categories.all.edges}" should have 3 items
    And I can see 3 categories with no more than 2 posts ordered by "title"