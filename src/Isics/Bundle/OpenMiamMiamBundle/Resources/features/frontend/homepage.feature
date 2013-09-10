Feature: Branch homepage
  As a customer
  I can see branch name

  Background:
    Given an association "Friends of organic food"
    And association "Friends of organic food" has following branches:
      | name     |
      | Branch 1 |
      | Branch 2 |

  Scenario Outline: Branch homepage title
    Given I am on "<url>"
    Then the response status code should be 200
    And the "h1" element should contain "<title>"

  Examples:
    | url       | title    |
    | /branch-1 | Branch 1 |
    | /branch-2 | Branch 2 |

  Scenario: Branch homepage for a nonexistent branch
    Given I am on "/foobar"
    Then the response status code should be 404
