Feature: Branch homepage
    As a customer
    I can see branch name

    Background:
        Given an association "Comptoir Bio"
        And association "Comptoir Bio" has following branches:
            | name                   |
            | Charleville-Mézières   |
            | Le Chesnois Auboncourt |

    Scenario Outline: Branch homepage title
        Given I am on "<url>"
         Then the response status code should be 200
          And the "h1" element should contain "<title>"

        Examples:
            | url                     | title                  |
            | /charleville-mezieres   | Charleville-Mézières   |
            | /le-chesnois-auboncourt | Le Chesnois Auboncourt |

    Scenario: Branch homepage for a nonexistent branch
        Given I am on "/foobar"
         Then the response status code should be 404