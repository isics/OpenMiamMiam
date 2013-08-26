Feature: Branch cart
    As a customer
    I can add products to cart
    In order to checkout

    Background:
      Given there are following categories:
            | name              |
            | Fruits et Légumes |
        And there are following producers:
            | name        |
            | Beth Rave   |
        And producer "Beth Rave" has following products:
            | name               | category          | description | price |
            | Panier de légumes  | Fruits et Légumes |             |    15 |
        And an association "L'asso Sisson"
        And association "L'asso Sisson" has following branches:
            | name  |
            | Lorem |
            | Ipsum |
        And branch "Lorem" has following calendar:
            | date                | from   | to     |
            | last wednesday      | 5 p.m. | 7 p.m. |
            | wednesday           | 5 p.m. | 7 p.m. |
            | wednesday + 1 week  | 5 p.m. | 7 p.m. |
            | wednesday + 2 weeks | 5 p.m. | 7 p.m. |
        And branch "Ipsum" has following calendar:
            | date                | from   | to     |
            | last wednesday      | 5 p.m. | 7 p.m. |
            | wednesday + 1 week  | 5 p.m. | 7 p.m. |
            | wednesday + 3 weeks | 5 p.m. | 7 p.m. |
        And branch "Lorem" has following producers:
            | name        |
            | Beth Rave   |
        And branch "Lorem" has following products:
            | producer    | product           |
            | Beth Rave   | Panier de légumes |

    Scenario Outline: See empty branch cart summary
        Given I am on "<url>"
         Then I should see "My cart (0)"
          And I should see the next date "<date>" formated "m-d"

        Examples:
            | url    | date               |
            | /lorem | wednesday          |
            | /ipsum | wednesday + 1 week |

    Scenario: Add a product via product page
        Given I am on "/lorem/fruits-et-legumes"
          And I follow "Panier de légumes"
         When I press "Add to cart"
         Then I should be on "/lorem/cart"
          And I should see "My cart (1) €15.00"
          And I should see "Item has been added to cart."

    Scenario: Add an existing product via product page
        Given I am on "/lorem/fruits-et-legumes"
          And I follow "Panier de légumes"
          And I press "Add to cart"
          And I follow "Panier de légumes"
         When I press "Add to cart"
         Then I should be on "/lorem/cart"
          And I should see "My cart (1) €30.00"
          And I should see "Item has been added to cart."

    Scenario: Update quantity
        Given I am on "/lorem/fruits-et-legumes"
          And I follow "Panier de légumes"
          And I press "Add to cart"
          And I change quantity to "3"
         When I press "Update"
         Then I should see "My cart (1) €45.00"
          And I should see "Cart has been updated."

    Scenario: Reset quantity (remove)
        Given I am on "/lorem/fruits-et-legumes"
          And I follow "Panier de légumes"
          And I press "Add to cart"
          And I change quantity to "0"
         When I press "Update"
         Then I should see "My cart (0)"
          And I should see "Cart has been updated."
