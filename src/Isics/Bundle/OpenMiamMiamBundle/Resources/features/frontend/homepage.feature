Feature: Branch homepage
  As a customer
  I can see branch name

  Background:
    Given an association "Friends of organic food"
    And association "Friends of organic food" has following branches:
      | city   | department_number |
      | City 1 | 29                |
      | City 2 | 81                |

  Scenario Outline: Branch homepage title
    Given I am on "<url>"
    Then the response status code should be 200
    And the "h1" element should contain "<title>"

  Examples:
    | url     | title  |
    | /city-1 | City 1 |
    | /city-2 | City 2 |

  Scenario: Branch homepage for a nonexistent branch
    Given I am on "/foobar"
    Then the response status code should be 404
