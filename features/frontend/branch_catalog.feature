Feature: Branch catalog
    As a customer
    I need to be able to browse products of a branch
    In order to checkout

    Background:
        Given there are following categories:
            | name     |
            | Laitages |
            | Poisson  |
            | Viande   |
        And there are following producers:
            | name               |
            | Frédéric Lefebvre  |
            | GAEC du Mont Fossé |
        And producer "Frédéric Lefebvre" has following products:
            | name               | category | price | availability |
            | Merguez à la pièce | Viande   |       |            3 |
        And producer "GAEC du Mont Fossé" has following products:
            | name               | category | price | availability |
            | Yahourt nature     | Laitages |  0.37 |            3 |
        And an association "Comptoir Bio"
        And association "Comptoir Bio" has following branches:
            | name                   |
            | Charleville-Mézières   |
            | Le Chesnois Auboncourt |
        And association "Comptoir Bio" has following producers:
            | name               |
            | Frédéric Lefebvre  |
            | GAEC du Mont Fossé |
        And branch "Charleville-Mézières" has following producers:
            | name               |
            | Frédéric Lefebvre  |
            | GAEC du Mont Fossé |
        And branch "Le Chesnois Auboncourt" has following producers:
            | name               |
            | Frédéric Lefebvre  |
            | GAEC du Mont Fossé |
        And branch "Charleville-Mézières" has following products:
            | producer           | product            |
            | Frédéric Lefebvre  | Merguez à la pièce |
            | GAEC du Mont Fossé | Yahourt nature     |
        And branch "Le Chesnois Auboncourt" has following products:
            | producer           | product            |
            | GAEC du Mont Fossé | Yahourt nature     |

    Scenario: See categories that have products 1/2
        Given I am on "/charleville-mezieres"
         Then I should see "Laitages"
          And I should see "Viande"
          But I should not see "Poisson"

    Scenario: See categories that have products 2/2
        Given I am on "/le-chesnois-auboncourt"
         Then I should see "Laitages"
          But I should not see "Viande"
          And I should not see "Poisson"

    Scenario: See products of a category
        Given I am on "/charleville-mezieres"
         When I follow "Viande"
         Then I should see "Merguez à la pièce"
          But I should not see "Yahout nature"