// Teste funcional: o fluxo principal do sistema, do pedido à NFC-e simulada.
describe('Pedido até a nota fiscal', () => {
    beforeEach(() => {
        cy.login();
    });

    it('cria um pedido, gera a NFC-e simulada e mostra o comprovante com a tarja', () => {
        cy.visit('/pedidos/novo');

        cy.get('#order-search').type('38');
        cy.get('[aria-label^="Adicionar Carnes c/ Legumes Especial - Grande"]').click();
        cy.get('[aria-label="Aumentar quantidade de Carnes c/ Legumes Especial - Grande"]').click();
        cy.get('#order-search').clear().type('cerveja lata');
        cy.get('[aria-label^="Adicionar Cerveja Lata 350ml"]').click();

        cy.get('#order-lines li').should('have.length', 2);
        cy.get('#order-total').should('contain', 'R$ 108,00');

        cy.get('#payment_method').select('Dinheiro');
        cy.get('#amount_received').type('200');
        cy.get('#order-change').should('contain', 'R$ 92,00');
        cy.get('#consumer_cpf').type('529.982.247-25');

        cy.contains('button', 'Fechar pedido').click();
        cy.location('pathname').should('match', /^\/pedidos\/\d+$/);

        cy.issueInvoice();
        cy.contains('a', 'Baixar XML').should('have.attr', 'href').and('match', /\/nota\/xml$/);

        cy.contains('a', 'Imprimir / salvar PDF').click();
        cy.location('pathname').should('match', /\/nota\/comprovante$/);
        cy.get('main.receipt').within(() => {
            cy.contains('DANFE NFC-e SIMULADO — SEM VALOR FISCAL');
            cy.contains('EMITIDA EM AMBIENTE DE HOMOLOGAÇÃO – SEM VALOR FISCAL');
            cy.contains('Cerveja Lata 350ml');
            cy.contains('108,00');
            cy.contains('92,00');
            cy.contains('CONSUMIDOR CPF: 529.982.247-25');
            cy.get('.qr-zone img').should('be.visible');
        });
    });

    it('mostra o pedido com a nota no histórico', () => {
        cy.closeOrder({ payment: '17', cpf: null });
        cy.issueInvoice();
        cy.location('pathname').then((path) => {
            const order = path.match(/\d+/)[0];

            cy.visit('/pedidos');
            cy.get(`[aria-label="Abrir pedido ${order}"]`).closest('tr').should('contain', 'Gerada');
        });
    });
});
