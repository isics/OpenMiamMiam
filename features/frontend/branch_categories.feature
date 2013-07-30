Feature: Branch categories
    As a customer
    I need to see product categories of a branch
    In order to checkout

    Background:
        Given there are following categories:
            | name     |
            | Laitages |
            | Poisson  |
            | Viande   |
        And an association "Comptoir Bio"
        And association "Comptoir Bio" has following branches:
            | name                   |
            | Charleville-Mézières   |
            | Le Chesnois Auboncourt |
        And association "Comptoir Bio" has following producers:
            | name               |
            | Frédéric Lefebvre  |
            | GAEC du Mont Fossé |
        And producer "Frédéric Lefebvre" has following products:
            | name               | category | price | branch                 |
            | Merguez à la pièce | Viande   |       | Le Chesnois Auboncourt |
        And producer "GAEC du Mont Fossé" has following products:
            | name               | category | price | branch                 |
            | Yahout nature      | Laitages |  0.37 | Le Chesnois Auboncourt |

    Scenario: See categories that have products
        Given I am on the branch "Le Chesnois Auboncourt" homepage
         Then I should see "Laitages" and "Viande"
          But I should not see "Poisson"