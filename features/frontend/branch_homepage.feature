Feature: Branch homepage
    As a customer
    I can see branch name

    Background:
        Given an association "L'asso Sisson"
        And association "L'asso Sisson" has following branches:
            | name  |
            | Lorem |
            | Ipsum |

    Scenario Outline: Branch homepage title
        Given I am on "<url>"
         Then the response status code should be 200
          And the "h1" element should contain "<title>"

        Examples:
            | url    | title |
            | /lorem | Lorem |
            | /ipsum | Ipsum |

    Scenario: Branch homepage for a nonexistent branch
        Given I am on "/foobar"
         Then the response status code should be 404