import { defineConfig } from 'cypress';

// Os testes E2E rodam no navegador contra o sistema já no ar (docker compose up),
// sempre com a NFC-e simulada. Outro endereço: CYPRESS_BASE_URL=https://... npm run test:e2e
export default defineConfig({
    e2e: {
        baseUrl: 'http://localhost:8000',
        specPattern: 'cypress/e2e/**/*.cy.js',
        supportFile: 'cypress/support/e2e.js',
        viewportWidth: 1280,
        viewportHeight: 900,
    },
    env: {
        // Usuário criado pelo DatabaseSeeder; troque por CYPRESS_OWNER_EMAIL e CYPRESS_OWNER_PASSWORD.
        OWNER_EMAIL: 'proprietaria@restaurante.test',
        OWNER_PASSWORD: 'senha1234',
    },
    video: false,
    // storage/logs já é ignorado pelo git.
    screenshotsFolder: 'storage/logs/cypress/screenshots',
    downloadsFolder: 'storage/logs/cypress/downloads',
});
