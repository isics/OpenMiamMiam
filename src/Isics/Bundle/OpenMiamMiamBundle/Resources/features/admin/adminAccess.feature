Feature: Admin authentication & authorization
  In order to manage my informations
  I want to connect to the administration area

  Background:
    Given there are following users:
      | email          | password | firstname | lastname | address_1             | address_2       | zip_code | city      |
      | foo@bar.com    | secret1  | Foo       | Bar      | First line of address | Second line of  | AA9A 9AA | York      |
      | john@doe.com   | secret2  | John      | Doe      | First line of address | Second line of  | AA9A 9AA | Liverpool |
      | john@smith.com | secret3  | John      | Smith    | First line of address | Second line of  | AA9A 9AA | London    |
    Given there are following producers:
      | name        | managers                  |
      | Beth Rave   | john@doe.com              |
      | Elsa Dorsa  | foo@bar.com, john@doe.com |
      | Romeo Frigo |                           |

  Scenario: Access to the login page
    Given I am on "/admin/login"
    Then I should be on "/admin/login"
    And I should see "Manager access"

  Scenario: Redirect to the login
    Given I am on "/admin"
    Then I should be on "/admin/login"
    And I should see "Manager access"

  Scenario: Log to the administration area but have no credentials
    Given I am on "/admin"
    And I should see "Manager access"
    When I fill username field with "john@smith.com"
    And I fill password field with "secret3"
    And I press login button
    Then the response status code should be 403

  Scenario: Redirect to producer's administration area after logged in
    Given I am on "/admin"
    And I should see "Manager access"
    When I fill username field with "foo@bar.com"
    And I fill password field with "secret1"
    And I press login button
    Then I should be on "/admin/producers/2/dashboard"
    And the administration area switcher should be on "Elsa Dorsa"

  Scenario: Choose administration area after logged in
    Given I am on "/admin"
    And I should see "Manager access"
    When I fill username field with "john@doe.com"
    And I fill password field with "secret2"
    And I press login button
    Then I should be on "/admin/"
    And I should see "Choose a role"
    When I follow "Elsa Dorsa"
    Then I should be on "/admin/producers/2/dashboard"
    And the administration area switcher should be on "Elsa Dorsa"

  Scenario: Attempt to access not permitted administration area after login
    Given I am on "/admin"
    And I should see "Manager access"
    When I fill username field with "foo@bar.com"
    And I fill password field with "secret1"
    And I press login button
    Then I should be on "/admin/producers/2/dashboard"
    When I go to "/admin/producers/3/dashboard"
    Then the response status code should be 403
