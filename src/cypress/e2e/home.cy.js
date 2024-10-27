describe('HomeComponent', () => {
    const email = 'alice@mail.com'
    const password = '123456'

    beforeEach(() => {
        // Perform login before each test
        cy.visit('/login')
        cy.get('input#email').type(email)
        cy.get('input#password').type(password)
        cy.get('button[type="submit"]').click()

        // Confirm successful login
        cy.url().should('include', '/home')
        cy.contains('Reminders Home Page').should('be.visible')
    })

    it('displays the reminders list', () => {
        cy.get('.grid').should('be.visible')

        // If reminders exist, they should be displayed
        cy.get('.grid').find('.bg-white').should('have.length.greaterThan', 0)
    })

    it('opens and closes the add reminder modal', () => {
        // Click the create reminder button
        cy.get('button[data-cy="add-reminder-button"]').debug().click()

        // Modal should appear
        cy.get('form').should('be.visible')
        cy.contains('Add New Reminder').should('be.visible')

        // Close the modal
        cy.contains('Cancel').click()

        // Modal should not be visible
        cy.get('form').should('not.exist')
    })

    it('adds a new reminder', () => {
        // Click the create reminder button
        cy.get('button[data-cy="add-reminder-button"]').debug().click()

        // Modal should appear
        cy.get('form').should('be.visible')
        cy.contains('Add New Reminder').should('be.visible')

        // Calculate 10 seconds from now
        const now = new Date();
        now.setSeconds(now.getSeconds() + 60);

        // Format the date and time in the required format (YYYY-MM-DDTHH:mm)
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');

        // Combine to match the input format
        const dateTime = `${year}-${month}-${day}T${hours}:${minutes}`;

        // Fill in the form
        cy.get('input#title').type('Test Reminder')
        cy.get('textarea#description').type('This is a test reminder description.')
        cy.get('input#event_at').type(dateTime)
        cy.get('input#remind_at').type(dateTime)

        // Submit the form
        cy.get('button').contains('Add').click()

        // Modal should close
        cy.get('form').should('not.exist')

        // New reminder should appear in the list
        cy.contains('Test Reminder').should('be.visible')
        cy.contains('This is a test reminder description.').should('be.visible')
    })

    it('viewing a reminder', () => {
        // Click the create reminder button
        cy.get('button.text-green-500').first().click()

        // Modal should appear
        cy.get('#view-modal').first().should('be.visible')
        cy.contains('Test Reminder').should('be.visible')

        // Modal should close
        cy.get('form').should('not.exist')
    })

    it('edits an existing reminder', () => {
        // Open the edit modal for the first reminder
        cy.get('button.text-blue-500').first().click();

        // Verify the edit modal appears and modify fields
        cy.get('form').should('be.visible');
        cy.contains('Edit Reminder').should('be.visible');

        // Update title and description
        cy.get('input#title').clear().type('Updated Reminder Title');
        cy.get('textarea#description').clear().type('Updated description for the test reminder.');

        // Submit the updated reminder
        cy.get('button').contains('Update').click();

        // Modal should close, and the updated reminder should appear in the list
        cy.get('form').should('not.exist');
        cy.contains('Updated Reminder Title').should('be.visible');
        cy.contains('Updated description for the test reminder.').should('be.visible');
    });

    it('deletes a reminder', () => {
        // Open delete confirmation for the first reminder
        cy.get('button.text-red-500').first().click();

        // Confirm delete modal appears
        cy.contains('Confirm Deletion').should('be.visible');

        // Confirm the deletion
        cy.get('button').contains('Delete').click();

        // Modal should close, and the reminder should no longer be in the list
        cy.get('form').should('not.exist');
        cy.contains('Updated Reminder Title').should('not.exist');
    });
})
