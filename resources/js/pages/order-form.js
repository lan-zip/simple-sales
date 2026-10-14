const form = document.querySelector('[data-order-form]');

if (form) {
    const list = form.querySelector('[data-product-list]');
    const template = form.querySelector('[data-product-template]');
    const feedback = form.querySelector('[data-order-feedback]');
    let nextIndex = Math.max(...Array.from(list.children, row => Number(row.dataset.rowIndex))) + 1;
    const formatMoney = cents => {
        const integer = cents / 100n;
        const fraction = cents % 100n;
        return `Rp ${integer.toLocaleString('id-ID')}${fraction ? ',' + fraction.toString().padStart(2, '0') : ''}`;
    };
    const toCents = raw => {
        if (!/^\d+(\.\d{1,2})?$/.test(raw)) return null;
        const [whole, fraction = ''] = raw.split('.');
        return BigInt(whole) * 100n + BigInt(fraction.padEnd(2, '0'));
    };
    function recalculate() {
        let total = 0n;
        let complete = true;
        const rows = list.querySelectorAll('[data-product-row]');
        rows.forEach(row => {
            const selected = row.querySelector('[data-product-select]').selectedOptions[0];
            const rawQuantity = row.querySelector('[data-product-quantity]').value;
            const price = toCents(selected?.dataset.price ?? '');
            const quantity = /^\d+$/.test(rawQuantity) ? BigInt(rawQuantity) : 0n;
            const subtotal = price !== null && quantity > 0n ? price * quantity : null;
            row.querySelector('[data-product-price]').textContent = price === null ? '—' : formatMoney(price);
            row.querySelector('[data-product-subtotal]').textContent = subtotal === null ? '—' : formatMoney(subtotal);
            row.querySelector('[data-product-stock]').textContent = selected?.dataset.stock !== undefined ? `Stok tersedia: ${selected.dataset.stock} ${selected.dataset.unit}` : '';
            row.querySelector('[data-remove-product]').disabled = rows.length === 1;
            if (subtotal === null) complete = false;
            else total += subtotal;
        });
        form.querySelector('[data-order-total]').textContent = complete ? formatMoney(total) : 'Lengkapi produk dan jumlah';
    }
    form.querySelector('[data-add-product]').addEventListener('click', () => {
        const fragment = template.content.cloneNode(true);
        // Replace identifiers, never interpolate product data into HTML strings.
        fragment.querySelectorAll('*').forEach(element => {
            for (const attribute of [...element.attributes]) {
                if (attribute.value.includes('__INDEX__')) element.setAttribute(attribute.name, attribute.value.replaceAll('__INDEX__', String(nextIndex)));
            }
        });
        const row = fragment.querySelector('[data-product-row]');
        list.append(fragment);
        nextIndex++;
        recalculate();
        row.querySelector('select').focus();
        feedback.textContent = 'Baris produk ditambahkan.';
    });
    list.addEventListener('click', event => {
        const button = event.target.closest('[data-remove-product]');
        if (!button || list.children.length <= 1) return;
        const row = button.closest('[data-product-row]');
        const next = row.nextElementSibling || row.previousElementSibling;
        row.remove();
        recalculate();
        next?.querySelector('select').focus();
        feedback.textContent = 'Baris produk dihapus.';
    });
    list.addEventListener('input', recalculate);
    list.addEventListener('change', recalculate);
    form.querySelector('[data-order-summary]').hidden = false;
    form.classList.add('order-form-ready');
    recalculate();
}
