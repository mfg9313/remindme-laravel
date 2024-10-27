import { defineConfig } from "cypress";

export default defineConfig({
  e2e: {
      baseUrl: 'http://localhost:8000',
    setupNodeEvents(on, config) {
    },
      specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
      supportFile: 'cypress/support/e2e.js',
  },
});
