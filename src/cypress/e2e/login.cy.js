describe('LoginComponent', () => {
    beforeEach(() => {
        // Visit the login page before each test
        cy.visit('/login')

        // Ignore the NavigationDuplicated error
        cy.on('uncaught:exception', (err) => {
            return !err.message.includes('Avoided redundant navigation to current location');
        });
    })

    it('displays the login form', () => {
        cy.get('form').should('be.visible')
        cy.get('input#email').should('be.visible')
        cy.get('input#password').should('be.visible')
        cy.get('button[type="submit"]').should('be.visible').and('contain', 'Sign in')
    })

    it('successfully logs in with valid credentials', () => {
        // Replace with valid credentials for your application
        const validEmail = 'alice@mail.com'
        const validPassword = '123456'

        cy.get('input#email').type(validEmail)
        cy.get('input#password').type(validPassword)
        cy.get('button[type="submit"]').click()

        // After successful login, expect redirected to home page
        cy.url().should('include', '/home')

        // Check for a welcome message
        cy.contains('Reminders Home Page').should('be.visible')
    })

    it('shows error message with invalid credentials', () => {
        // Invalid credentials
        const invalidEmail = 'invalid@example.com'
        const invalidPassword = 'wrongpassword'

        cy.get('input#email').type(invalidEmail)
        cy.get('input#password').type(invalidPassword)
        cy.get('button[type="submit"]').click()

        // Expect an error message to be displayed
        cy.get('.text-red-500').should('be.visible').and('contain', 'incorrect username or password')

        // Ensure the URL remains on the login page
        cy.url().should('include', '/login')
    })
})
