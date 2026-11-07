// Teste não funcional: verificação automática de acessibilidade (WCAG 2.1 A e AA) com axe.
describe('Acessibilidade', () => {
    beforeEach(() => {
        cy.login();
    });

    it('tela do pedido com a nota fiscal sem violações graves', () => {
        cy.closeOrder();
        cy.issueInvoice();
        cy.checkAccessibility();
    });

    it('comprovante da nota sem violações graves', () => {
        cy.closeOrder();
        cy.issueInvoice();
        cy.contains('a', 'Imprimir / salvar PDF').click();
        cy.checkAccessibility();
    });

    it('tela de novo pedido sem violações graves', () => {
        cy.visit('/pedidos/novo');
        cy.get('[aria-label^="Adicionar Carnes c/ Legumes Especial - Grande"]').click();
        cy.checkAccessibility();
    });

    it('histórico de pedidos e notas sem violações graves', () => {
        cy.visit('/pedidos');
        cy.checkAccessibility();
    });

    it('login sem violações graves', () => {
        cy.clearAllSessionStorage();
        cy.clearCookies();
        cy.visit('/login');
        cy.checkAccessibility();
    });
});
