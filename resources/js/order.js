// Tela de novo pedido: monta a comanda e calcula o que é exibido.
// Os valores gravados são sempre recalculados no servidor.

const money = (cents) => 'R$ ' + (cents / 100).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

function setupOrderForm(form) {
    const lines = document.getElementById('order-lines');
    const empty = document.getElementById('order-empty');
    const totalOutput = document.getElementById('order-total');
    const changeOutput = document.getElementById('order-change');
    const payment = document.getElementById('payment_method');
    const cashFields = document.getElementById('cash-fields');
    const received = document.getElementById('amount_received');
    const addButtons = [...document.querySelectorAll('[data-add-variation]')];
    const items = new Map();
    let nextIndex = 0;

    function iconButton(text, label, onClick) {
        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = text;
        button.setAttribute('aria-label', label);
        button.className = 'shrink-0 w-7 h-7 rounded border border-gray-300 text-gray-800 font-bold hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gold-500';
        button.addEventListener('click', onClick);
        return button;
    }

    function hiddenInput(name, value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        return input;
    }

    function render() {
        let total = 0;
        lines.replaceChildren();

        for (const item of items.values()) {
            total += item.priceCents * item.quantity;

            const row = document.createElement('li');
            row.className = 'py-2 space-y-1';

            const name = document.createElement('p');
            name.className = 'text-sm font-medium text-gray-900';
            name.textContent = item.label;

            const controls = document.createElement('div');
            controls.className = 'flex items-center gap-2';

            const quantity = document.createElement('span');
            quantity.className = 'w-6 text-center text-sm';
            quantity.textContent = item.quantity;

            const subtotal = document.createElement('span');
            subtotal.className = 'ml-auto text-sm font-semibold text-gray-900';
            subtotal.textContent = money(item.priceCents * item.quantity);

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.textContent = 'Remover';
            remove.setAttribute('aria-label', `Remover ${item.label}`);
            remove.className = 'text-xs font-medium text-red-700 hover:underline focus:outline-none focus:ring-2 focus:ring-gold-500 rounded';
            remove.addEventListener('click', () => {
                items.delete(item.id);
                render();
                // A linha sumiu: o foco não pode se perder com ela.
                document.getElementById('order-search').focus();
            });

            controls.append(
                iconButton('−', `Diminuir quantidade de ${item.label}`, () => changeQuantity(item, -1)),
                quantity,
                iconButton('+', `Aumentar quantidade de ${item.label}`, () => changeQuantity(item, 1)),
                remove,
                subtotal,
                hiddenInput(`items[${item.index}][variation_id]`, item.id),
                hiddenInput(`items[${item.index}][quantity]`, item.quantity),
            );

            row.append(name, controls);
            row.dataset.variationId = item.id;
            lines.append(row);
        }

        empty.classList.toggle('hidden', items.size > 0);
        totalOutput.textContent = money(total);

        const isCash = payment.value === form.dataset.cashMethod;
        cashFields.classList.toggle('hidden', ! isCash);
        received.required = isCash;

        const receivedCents = Math.round(Number(received.value || 0) * 100);
        changeOutput.textContent = money(Math.max(receivedCents - total, 0));
    }

    function changeQuantity(item, delta) {
        // A quantidade mínima é 1; para tirar o item existe o botão Remover.
        item.quantity = Math.min(Math.max(item.quantity + delta, 1), 999);
        render();
        const label = delta > 0 ? 'Aumentar' : 'Diminuir';
        lines.querySelector(`[data-variation-id="${item.id}"] [aria-label^="${label}"]`).focus();
    }

    function add(button, quantity = 1) {
        const id = button.dataset.variationId;
        const item = items.get(id);

        if (item) {
            item.quantity = Math.min(item.quantity + quantity, 999);
            return;
        }

        items.set(id, {
            id,
            index: nextIndex++,
            label: button.dataset.label,
            priceCents: Number(button.dataset.priceCents),
            quantity,
        });
    }

    addButtons.forEach((button) => {
        button.addEventListener('click', () => {
            add(button);
            render();
        });
    });

    // Volta com erro de validação: remonta a comanda para ninguém perder o pedido.
    JSON.parse(form.dataset.oldItems).forEach((old) => {
        const button = addButtons.find((candidate) => candidate.dataset.variationId === String(old.variation_id));
        if (button) {
            add(button, Number(old.quantity) || 1);
        }
    });

    payment.addEventListener('change', render);
    received.addEventListener('input', render);

    const search = document.getElementById('order-search');
    const searchEmpty = document.getElementById('order-search-empty');

    search.addEventListener('input', () => {
        const term = search.value.trim().toLowerCase();

        document.querySelectorAll('[data-order-product]').forEach((product) => {
            product.classList.toggle('hidden', ! product.dataset.search.includes(term));
        });

        let anyVisible = false;
        document.querySelectorAll('[data-order-category]').forEach((category) => {
            const visible = category.querySelector('[data-order-product]:not(.hidden)') !== null;
            category.classList.toggle('hidden', ! visible);
            anyVisible = anyVisible || visible;
        });
        searchEmpty.classList.toggle('hidden', anyVisible);
    });

    render();
    document.getElementById('order-errors')?.focus();
}

const orderForm = document.getElementById('order-form');

if (orderForm) {
    setupOrderForm(orderForm);
}
