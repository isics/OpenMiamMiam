Feature: Branch catalog
  As a customer
  I need to be able to browse products of a branch
  In order to checkout

  Background:
    Given there are following categories:
      | name                  |
      | Fruits and vegetables |
      | Dairy produce         |
      | Meat                  |
    And there are following producers:
      | name        |
      | Beth Rave   |
      | Elsa Dorsa  |
      | Romeo Frigo |
    And producer "Beth Rave" has following products:
      | name                 | category              | description | price | availability            |
      | Basket of vegetables | Fruits and vegetables |             | 15.0  | available               |
    And producer "Elsa Dorsa" has following products:
      | name                 | category              | description | price | availability            |
      | Prime rib of beef    | Meat                  |             |       | available at next month |
      | Sausages             | Meat                  | 100% lamb   |       | available               |
    And producer "Romeo Frigo" has following products:
      | name                 | category              | description | price | availability            |
      | Butter               | Dairy produce         |             | 0.40  | 14 in stock             |
      | Plain yoghurt        | Dairy produce         |             | 0.50  | 0 in stock              |
      | Fruit yoghurt        | Dairy produce         |             | 0.60  | unavailable             |
    And an association "Friends of organic food"
    And association "Friends of organic food" has following branches:
      | name     |
      | Branch 1 |
      | Branch 2 |
    And branch "Branch 1" has following calendar:
      | date     | from   | to     |
      | + 1 week | 5 p.m. | 7 p.m. |
    And association "Friends of organic food" has following producers:
      | name        |
      | Beth Rave   |
      | Elsa Dorsa  |
      | Romeo Frigo |
    And branch "Branch 1" has following producers:
      | name        |
      | Beth Rave   |
      | Elsa Dorsa  |
      | Romeo Frigo |
    And branch "Branch 2" has following producers:
      | name        |
      | Beth Rave   |
      | Elsa Dorsa  |
      | Romeo Frigo |
    And branch "Branch 1" has following products:
      | producer    | product              |
      | Beth Rave   | Basket of vegetables |
      | Elsa Dorsa  | Prime rib of beef    |
      | Elsa Dorsa  | Sausages             |
      | Romeo Frigo | Butter               |
      | Romeo Frigo | Plain yoghurt        |
      | Romeo Frigo | Fruit yoghurt        |
    And branch "Branch 2" has following products:
      | producer    | product              |
      | Beth Rave   | Basket of vegetables |
      | Romeo Frigo | Butter               |
      | Romeo Frigo | Plain yoghurt        |
      | Romeo Frigo | Fruit yoghurt        |

  Scenario: See categories that have products 1/2
    Given I am on "/branch-1"
    Then I should see "Fruits and vegetables"
    And I should see "Dairy produce"
    And I should see "Meat"

  Scenario: See categories that have products 2/2
    Given I am on "/branch-2"
    Then I should see "Fruits and vegetables"
    And I should see "Dairy produce"
    But I should not see "Meat"

  Scenario: See products of a category
    Given I am on "/branch-1"
    When I follow "Meat"
    Then I should see "Sausages"
    But I should not see "Yahout nature"

  Scenario: Category with no product
    Given I am on "/branch-2/meat"
    Then the response status code should be 404

  Scenario: Not see products unavailable
    Given I am on "/branch-1/dairy-produce"
    Then I should see "Butter"
    And I should see "Plain yoghurt"
    But I should not see "Fruit yoghurt"

  Scenario: See product details
    Given I am on "/branch-1/meat"
    When I follow "Sausages"
    Then I should see "100% lamb"
