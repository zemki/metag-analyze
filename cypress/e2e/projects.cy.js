describe('Projects Management', () => {
  beforeEach(() => {
    // Log in before each test
    cy.visit('/login')
    cy.get('input[name="email"]').type('belli@uni-bremen.de')
    cy.get('input[name="password"]').type('your-password')
    cy.get('button[type="submit"]').click()
    
    // Verify successful login
    cy.url().should('include', '/dashboard')
  })

  it('should display the projects list', () => {
    cy.visit('/projects')
    
    // Verify elements of the projects list are visible
    cy.contains('Projects').should('be.visible')
    cy.get('li').should('have.length.at.least', 1)
  })

  it('should filter projects by search term', () => {
    cy.visit('/projects')
    
    // Get the number of initial projects
    cy.get('li').then($initialProjects => {
      const initialCount = $initialProjects.length
      
      // Enter a search term that should filter the list
      cy.get('input[id="search-studies"]').type('test')
      
      // Verify that the list is filtered
      cy.get('li').should($filteredProjects => {
        expect($filteredProjects.length).to.be.at.most(initialCount)
      })
    })
  })

  it('should navigate to project management page', () => {
    cy.visit('/projects')
    
    // Click on the manage project button of the first project
    cy.contains('Manage Project').first().click()
    
    // Verify that we navigated to the project detail page
    cy.url().should('include', '/projects/')
  })

  it('should create a new project', () => {
    cy.visit('/projects')
    
    // Click on the new project button
    cy.contains('New Project').click()
    
    // Verify navigation to project creation page
    cy.url().should('include', '/projects/new')
    
    // Fill in project details
    cy.get('input[name="name"]').type('Cypress Test Project')
    cy.get('textarea[name="description"]').type('This is a test project created by Cypress')
    
    // Add an input
    cy.get('button').contains('Add Input').click()
    cy.get('input[placeholder="Input Name"]').type('Test Input')
    cy.get('select').select('text')
    
    // Submit the form
    cy.get('button[type="submit"]').click()
    
    // Verify project was created
    cy.url().should('include', '/projects/')
    cy.contains('Cypress Test Project').should('be.visible')
  })
})
