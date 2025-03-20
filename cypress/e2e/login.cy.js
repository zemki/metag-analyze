describe('Authentication Tests', () => {
  it('should successfully log in with valid credentials', () => {
    cy.visit('/login')
    
    // Fill in the login form
    cy.get('input[name="email"]').type('belli@uni-bremen.de')
    cy.get('input[name="password"]').type('your-password')
    
    // Submit the form
    cy.get('button[type="submit"]').click()
    
    // Verify that login was successful
    cy.url().should('include', '/dashboard')
    cy.contains('Projects').should('be.visible')
  })

  it('should show error message with invalid credentials', () => {
    cy.visit('/login')
    
    // Fill in the login form with invalid credentials
    cy.get('input[name="email"]').type('wrong@example.com')
    cy.get('input[name="password"]').type('wrongpassword')
    
    // Submit the form
    cy.get('button[type="submit"]').click()
    
    // Verify that error message is displayed
    cy.contains('These credentials do not match our records').should('be.visible')
    cy.url().should('include', '/login')
  })
})
