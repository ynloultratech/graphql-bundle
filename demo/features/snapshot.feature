Feature: Schema Snapshot

  Scenario: Verify "default" Endpoint
    Given previous schema snapshot of "admin" endpoint
    When compare with current schema
    Then current schema is compatible with latest snapshot
    And current schema is same after latest snapshot

  Scenario: Verify "frontend" Endpoint
    Given previous schema snapshot of "frontend" endpoint
    When compare with current schema
    Then current schema is compatible with latest snapshot
    And current schema is same after latest snapshot
