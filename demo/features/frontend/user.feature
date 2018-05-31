Feature: User Forbidden

  Scenario: Get User list in `frontend` is Forbidden
    Given the operation named "UserList"
    And variable "first" is 5
    When send
    Then the response is GraphQL error with "Cannot query field"
