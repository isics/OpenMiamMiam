Feature: Branch catalog
    As a customer
    I need to be able to browse products of a branch
    In order to checkout

    Background:
        Given there are following categories:
            | name              |
            | Fruits et Légumes |
            | Laitages          |
            | Viande            |
        And there are following producers:
            | name        |
            | Beth Rave   |
            | Elsa Dorsa  |
            | Roméo Frigo |
        And producer "Beth Rave" has following products:
            | name               | category          | description | price | availability |
            | Panier de légumes  | Fruits et Légumes |             |    15 |            3 |
        And producer "Elsa Dorsa" has following products:
            | name               | category          | description | price | availability |
            | Côte de bœuf       | Viande            |             |       |            3 |
            | Merguez            | Viande            | 100% agneau |       |            3 |
        And producer "Roméo Frigo" has following products:
            | name               | category          | description | price | availability |
            | Beurre             | Laitages          |             |  0.40 |            3 |
            | Yahourt nature     | Laitages          |             |  0.50 |            3 |
            | Yahourt aux fruits | Laitages          |             |  0.60 |            3 |
        And an association "L'asso Sisson"
        And association "L'asso Sisson" has following branches:
            | name  |
            | Lorem |
            | Ipsum |
        And association "L'asso Sisson" has following producers:
            | name        |
            | Beth Rave   |
            | Elsa Dorsa  |
            | Roméo Frigo |
        And branch "Lorem" has following producers:
            | name        |
            | Beth Rave   |
            | Elsa Dorsa  |
            | Roméo Frigo |
        And branch "Ipsum" has following producers:
            | name        |
            | Beth Rave   |
            | Elsa Dorsa  |
            | Roméo Frigo |
        And branch "Lorem" has following products:
            | producer    | product            |
            | Beth Rave   | Panier de légumes  |
            | Elsa Dorsa  | Côte de bœuf       |
            | Elsa Dorsa  | Merguez            |
            | Roméo Frigo | Beurre             |
            | Roméo Frigo | Yahourt nature     |
            | Roméo Frigo | Yahourt aux fruits |
        And branch "Ipsum" has following products:
            | producer    | product            |
            | Beth Rave   | Panier de légumes  |
            | Roméo Frigo | Beurre             |
            | Roméo Frigo | Yahourt nature     |
            | Roméo Frigo | Yahourt aux fruits |

    Scenario: See categories that have products 1/2
        Given I am on "/lorem"
         Then I should see "Fruits et Légumes"
          And I should see "Laitages"
          And I should see "Viande"

    Scenario: See categories that have products 2/2
        Given I am on "/ipsum"
         Then I should see "Fruits et Légumes"
          And I should see "Laitages"
          But I should not see "Viande"

    Scenario: See products of a category
        Given I am on "/lorem"
         When I follow "Viande"
         Then I should see "Merguez"
          But I should not see "Yahout nature"

    Scenario: Category with no product
        Given I am on "/ipsum/viande"
         Then the response status code should be 404

    Scenario: See product details
        Given I am on "/lorem/viande"
         When I follow "Merguez"
         Then I should see "100% agneau"