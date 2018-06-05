@admin
Feature: User

  Scenario: User list
    Given the operation named "UserList"
    And variable "first" is 5
    When send
    Then the response is OK
    And grab "{response.data.users.all.pageInfo}" to use as "pageInfo"
    And "{pageInfo.startCursor}" should be equal to "Y3Vyc29yOjA="
    And "{pageInfo.endCursor}" should be equal to "Y3Vyc29yOjQ="
    And "{pageInfo.hasPreviousPage}" should be false
    And "{pageInfo.hasNextPage}" should be true
    And "{response.data.users.all.edges[0].node.login}" should be equal to "admin"
    And "{response.data.users.all.edges[1].node.profile.phone}" should be equal to "{@user1.getProfile().getPhone()}"
    And "{response.data.users.all.edges[1].node.profile.address.zipCode}" should be equal to "{@user1.getProfile().getAddress().getZipCode()}"
    And "{ search('data.users.all.edges[*].node.login', response) }" should contains this subset:
    """
    - admin
    - "{@user1.getUsername()}"
    """

  Scenario: User list ordered by Login
    Given "users" from repository "AppBundle:User" first 3 records ordered by "username:DESC"
    And the operation named "UserList"
    And variables:
    """
    first: 3
    orderBy:
      - {field: login, direction: DESC}
    """
    When send
    Then the response is OK
    And "{users[0].getUsername()}" should be equal to "{response.data.users.all.edges[0].node.login}"
    And "{users[1].getUsername()}" should be equal to "{response.data.users.all.edges[1].node.login}"
    And "{users[2].getUsername()}" should be equal to "{response.data.users.all.edges[2].node.login}"

  Scenario: User list pagination first N records after X cursor
    Given "users" from repository "AppBundle:User" first "3:3" records ordered by "username:ASC"
    And the operation named "UserList"
    And variables:
    """
    first: 3
    orderBy:
      - {field: login, direction: ASC}
    after: Y3Vyc29yOjI=
    """
    When send
    Then the response is OK
    And "{users[0].getUsername()}" should be equal to "{response.data.users.all.edges[0].node.login}"
    And "{users[1].getUsername()}" should be equal to "{response.data.users.all.edges[1].node.login}"
    And "{users[2].getUsername()}" should be equal to "{response.data.users.all.edges[2].node.login}"
    And "{response.data.users.all.pageInfo.startCursor}" should be equal to "Y3Vyc29yOjM="
    And "{response.data.users.all.pageInfo.endCursor}" should be equal to "Y3Vyc29yOjU="
    And "{response.data.users.all.pageInfo.hasPreviousPage}" should be true
    And "{response.data.users.all.pageInfo.hasNextPage}" should be true

  Scenario: User list pagination first N records before X cursor
    Given "users" from repository "AppBundle:User" first "3" records ordered by "username:ASC"
    And the operation named "UserList"
    And variables:
    """
    first: 3
    orderBy:
      - {field: login, direction: ASC}
    before: Y3Vyc29yOjc=
    """
    When send
    Then the response is OK
    And "{users[0].getUsername()}" should be equal to "{response.data.users.all.edges[0].node.login}"
    And "{users[1].getUsername()}" should be equal to "{response.data.users.all.edges[1].node.login}"
    And "{users[2].getUsername()}" should be equal to "{response.data.users.all.edges[2].node.login}"
    And "{response.data.users.all.pageInfo.startCursor}" should be equal to "Y3Vyc29yOjA="
    And "{response.data.users.all.pageInfo.endCursor}" should be equal to "Y3Vyc29yOjI="
    And "{response.data.users.all.pageInfo.hasNextPage}" should be true
    But "{response.data.users.all.pageInfo.hasPreviousPage}" should be false

  Scenario: User list pagination last N records after X cursor
    Given "users" from repository "AppBundle:User" first "3:8" records ordered by "username:ASC"
    And the operation named "UserList"
    And variables:
    """
    last: 3
    orderBy:
      - {field: login, direction: ASC}
    after: Y3Vyc29yOjU=
    """
    When send
    Then the response is OK
    And "{users[0].getUsername()}" should be equal to "{response.data.users.all.edges[0].node.login}"
    And "{users[1].getUsername()}" should be equal to "{response.data.users.all.edges[1].node.login}"
    And "{users[2].getUsername()}" should be equal to "{response.data.users.all.edges[2].node.login}"
    And "{response.data.users.all.pageInfo.startCursor}" should be equal to "Y3Vyc29yOjg="
    And "{response.data.users.all.pageInfo.endCursor}" should be equal to "Y3Vyc29yOjEw"
    And "{response.data.users.all.pageInfo.hasNextPage}" should be false
    But "{response.data.users.all.pageInfo.hasPreviousPage}" should be true

  Scenario: User list pagination last N records before X cursor
    Given "users" from repository "AppBundle:User" first "3:2" records ordered by "username:ASC"
    And the operation named "UserList"
    And variables:
    """
    last: 3
    orderBy:
      - {field: login, direction: ASC}
    before: Y3Vyc29yOjU=
    """
    When send
    Then the response is OK
    And "{users[0].getUsername()}" should be equal to "{response.data.users.all.edges[0].node.login}"
    And "{users[1].getUsername()}" should be equal to "{response.data.users.all.edges[1].node.login}"
    And "{users[2].getUsername()}" should be equal to "{response.data.users.all.edges[2].node.login}"
    And "{response.data.users.all.pageInfo.startCursor}" should be equal to "Y3Vyc29yOjI="
    And "{response.data.users.all.pageInfo.endCursor}" should be equal to "Y3Vyc29yOjQ="
    And "{response.data.users.all.pageInfo.hasNextPage}" should be true
    And "{response.data.users.all.pageInfo.hasPreviousPage}" should be true

  Scenario: Users by login
    Given the operation named "UsersByLogin"
    And variable "logins" is "{ ['admin', @user1.getUsername()] }"
    When send
    Then the response is OK
    And "{response.data.users.byLogin[0].login}" should be equal to "admin"
    And "{response.data.users.byLogin[1].login}" should be equal to "{@user1.getUsername()}"

  Scenario: Users by login in reverse order
    Given the operation named "UsersByLogin"
    And variable "logins" is "{ [@user1.getUsername(), 'admin'] }"
    When send
    Then the response is OK
    And "{response.data.users.byLogin[0].login}" should be equal to "{@user1.getUsername()}"
    And "{response.data.users.byLogin[1].login}" should be equal to "admin"

  Scenario: Users by type
    Given the operation named "UsersByType"
    When send
    Then the response is OK
    And "{response.data.users.all.edges[0].node.__typename}" should be equal to "AdminUser"
    And "{ search('data.users.all.edges[0].node.posts', response) }" should be null
    And "{response.data.users.all.edges[1].node.__typename}" should be equal to "CommonUser"
    And "{ search('data.users.all.edges[1].node.posts', response) }" should not be null

  Scenario: Admin users
    Given the operation named "AdminUsers"
    When send
    Then the response is OK
    And "{response.data.users.allAdmin.totalCount}" should be equal to "1"
    And "{response.data.users.allAdmin.edges[0].node.login}" should be equal to "admin"



