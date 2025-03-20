// ***********************************************
// This file defines custom commands and overwriting
// existing commands for Cypress testing.
// ***********************************************

// -- Login Command --
Cypress.Commands.add('login', (email, password) => {
  cy.visit('/login')
  cy.get('input[name="email"]').type(email)
  cy.get('input[name="password"]').type(password)
  cy.get('button[type="submit"]').click()
})

// -- Logout Command --
Cypress.Commands.add('logout', () => {
  cy.get('[data-testid="user-menu"]').click()
  cy.contains('Logout').click()
})

// -- Create Project Command --
Cypress.Commands.add('createProject', (name, description) => {
  cy.visit('/projects/new')
  cy.get('input[name="name"]').type(name)
  cy.get('textarea[name="description"]').type(description)
  cy.get('button[type="submit"]').click()
})

// -- Add an Input to a Project Command --
Cypress.Commands.add('addInput', (name, type) => {
  cy.get('button').contains('Add Input').click()
  cy.get('input[placeholder="Input Name"]').last().type(name)
  cy.get('select').last().select(type)
})
