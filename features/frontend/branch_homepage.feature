Feature: Branch homepage
    As a customer
    I can see branch name and next date

    Background:
        Given an association "Comptoir Bio"
        And association "Comptoir Bio" has following branches:
            | name                   |
            | Charleville-Mézières   |
            | Le Chesnois Auboncourt |

    Scenario Outline: Branch homepage title
        Given I am on "<branch_url>"
         Then I should see "<title>"

        Examples:
            | branch_url              | title                          |
            | /charleville-mezieres   | Branch: Charleville-Mézières   |
            | /le-chesnois-auboncourt | Branch: Le Chesnois Auboncourt |

    Scenario: Branch homepage for a nonexistent branch
        Given I am on "/foobar"
         Then the response status code should be 404