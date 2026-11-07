import 'cypress-axe';

// Login pela tela, guardado entre os testes para não repetir o formulário.
Cypress.Commands.add('login', () => {
    cy.session('proprietaria', () => {
        cy.visit('/login');
        cy.get('#email').type(Cypress.env('OWNER_EMAIL'));
        cy.get('#password').type(Cypress.env('OWNER_PASSWORD'), { log: false });
        cy.get('form').submit();
        cy.location('pathname').should('eq', '/dashboard');
    });
});

// Monta e fecha um pedido pela tela: prato #38 (Grande) x2 e uma cerveja em lata.
Cypress.Commands.add('closeOrder', ({ payment = '01', received = '200', cpf = '529.982.247-25' } = {}) => {
    cy.visit('/pedidos/novo');

    cy.get('#order-search').type('38');
    cy.get('[aria-label^="Adicionar Carnes c/ Legumes Especial - Grande"]').click();
    cy.get('[aria-label="Aumentar quantidade de Carnes c/ Legumes Especial - Grande"]').click();
    cy.get('#order-search').clear().type('cerveja lata');
    cy.get('[aria-label^="Adicionar Cerveja Lata 350ml"]').click();

    cy.get('#order-total').should('contain', '108,00');

    cy.get('#payment_method').select(payment);
    if (payment === '01') {
        cy.get('#amount_received').type(received);
    }
    if (cpf) {
        cy.get('#consumer_cpf').type(cpf);
    }

    cy.contains('button', 'Fechar pedido').click();
    cy.location('pathname').should('match', /^\/pedidos\/\d+$/);
    cy.contains('Pedido fechado.');
});

// Na tela do pedido: gera a NFC-e simulada.
Cypress.Commands.add('issueInvoice', () => {
    cy.contains('button', 'Gerar NFC-e simulada').click();
    cy.contains('NFC-e simulada disponível. Sem valor fiscal.');
});

// Só violações graves e críticas reprovam; o relatório lista todas no console do Cypress.
Cypress.Commands.add('checkAccessibility', () => {
    cy.injectAxe();
    cy.checkA11y(null, {
        runOnly: { type: 'tag', values: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'] },
        includedImpacts: ['critical', 'serious'],
    }, (violations) => {
        violations.forEach((violation) => {
            Cypress.log({
                name: 'a11y',
                message: `${violation.impact}: ${violation.id} (${violation.nodes.length}) ${violation.help}`,
            });
        });
    });
});
