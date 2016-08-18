Feature: Branch cart
  As a customer
  I can add products to cart
  In order to checkout

  Background:
    Given there are following categories:
      | name                  |
      | Fruits and vegetables |
    And there are following producers:
      | name      |
      | Beth Rave |
    And producer "Beth Rave" has following products:
      | name                 | category              | description | price |
      | Basket of vegetables | Fruits and vegetables |             | 15    |
    And an association "Friends of organic food"
    And association "Friends of organic food" has following producers:
      | name        |
      | Beth Rave   |
    And association "Friends of organic food" has following branches:
      | city   | department_number |
      | City 1 | 29                |
      | City 2 | 81                |
    And branch "City 1" has following producers:
      | name      |
      | Beth Rave |
    And branch "City 1" has following products:
      | producer  | product              |
      | Beth Rave | Basket of vegetables |
    And branch "City 2" has following producers:
      | name      |
      | Beth Rave |
    And branch "City 2" has following products:
      | producer  | product              |
      | Beth Rave | Basket of vegetables |
    And branch "City 1" has following calendar:
      | date      | from   | to     |
      | last week | 5 p.m. | 7 p.m. |
      | + 1 week  | 5 p.m. | 7 p.m. |
      | + 2 weeks | 5 p.m. | 7 p.m. |
      | + 3 weeks | 5 p.m. | 7 p.m. |
    And branch "City 2" has following calendar:
      | date      | from   | to     |
      | last week | 5 p.m. | 7 p.m. |
      | + 2 weeks | 5 p.m. | 7 p.m. |
      | + 4 weeks | 5 p.m. | 7 p.m. |
    And producer "Beth Rave" will be present to following occurrences:
      | city   | date     |
      | City 1 | + 1 week |

  Scenario Outline: See empty branch cart summary
    Given I am on "<url>"
    Then I should see "My cart"
    And I should see the next date "<date>" formated "d F"

  Examples:
    | url         | date      |
    | /city-1 | + 1 week  |
    | /city-2 | + 2 weeks |

  Scenario: Orders closed 1/2
    Given branch "City 1" has following calendar:
      | date     | from    | to     |
      | tomorrow | 12 a.m. | 2 a.m. |
    And I am on "/city-1"
    Then I should see the next date "+ 1 week" formated "d F"

  Scenario: Orders closed 2/2
    Given branch "City 1" has following calendar:
      | date      | from    | to         |
      | yesterday | 10 p.m. | 11:59 p.m. |
    And I am on "/city-1"
    Then I should see the next date "+ 1 week" formated "d F"

  Scenario: Add a product via category page
    Given I am on "/city-1/fruits-and-vegetables"
    When I press "Add"
    Then I should be on "/city-1/cart"
    And I should see "My cart (1) €15.00"
    And I should see "Item has been added to cart."

  Scenario: Add a product via product page
    Given I am on "/city-1/fruits-and-vegetables"
    And I follow "Basket of vegetables"
    When I press "Add"
    Then I should be on "/city-1/cart"
    And I should see "My cart (1) €15.00"
    And I should see "Item has been added to cart."

  Scenario: Add an existing product via product page
    Given I am on "/city-1/fruits-and-vegetables"
    And I follow "Basket of vegetables"
    And I press "Add"
    And I follow "Basket of vegetables"
    When I press "Add"
    Then I should be on "/city-1/cart"
    And I should see "My cart (1) €30.00"
    And I should see "Item has been added to cart."

  Scenario: Update quantity
    Given I am on "/city-1/fruits-and-vegetables"
    And I follow "Basket of vegetables"
    And I press "Add"
    And I change quantity to "3"
    When I press "update"
    Then I should see "My cart (1) €45.00"
    And I should see "Cart has been updated."

  Scenario: Reset quantity (remove)
    Given I am on "/city-1/fruits-and-vegetables"
    And I follow "Basket of vegetables"
    And I press "Add"
    And I change quantity to "0"
    When I press "update"
    Then I should not see "My cart (1)"
    And I should see "Cart has been updated."

  Scenario: Product available
    Given I am on "/city-1/basket-of-vegetables-1"
    Then I should see "Add"
    And I should see "Available"

  Scenario: Product available with stock management
    Given Product "Basket of vegetables" of producer "Beth Rave" has stock level "10"
    Given I am on "/city-1/basket-of-vegetables-1"
    Then I should see add to cart form
    And I should see "10 in stock"

  Scenario: Producer absent
    Given I am on "/city-2/basket-of-vegetables-1"
    Then I should not see add to cart form
    And I should see "Producer absent"

  Scenario: Product not yet available
    Given Product "Basket of vegetables" of producer "Beth Rave" will be available at "+ 3 weeks"
    And I am on "/city-1/basket-of-vegetables-1"
    Then I should not see add to cart form
    And I should see "Available at"

  Scenario: Product is out of stock
    Given Product "Basket of vegetables" of producer "Beth Rave" has stock level "0"
    And I am on "/city-1/basket-of-vegetables-1"
    Then I should not see add to cart form
    And I should see "Too late!"
