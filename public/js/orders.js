let productRowCounter = 1;

// Agregar nueva fila de producto
function addProductRow() {
    const container = document.getElementById('productsContainer');
    const firstRow = container.querySelector('.product-row');
    const newRow = firstRow.cloneNode(true);

    // Limpiar valores y restaurar estado
    const selects = newRow.querySelectorAll('select');
    const inputs = newRow.querySelectorAll('input');

    selects.forEach(select => {
        select.selectedIndex = 0;
        const stock = select.options[0]?.getAttribute('data-stock') || 0;
        const quantityInput = select.closest('.product-row').querySelector('.quantity-input');
        quantityInput.max = stock;
        quantityInput.value = '';
        quantityInput.disabled = stock == 0;
    });

    inputs.forEach(input => {
        if (input.classList.contains('price-display')) {
            input.value = '';
        } else if (!input.classList.contains('quantity-input')) {
            input.value = '';
        }
    });

    container.appendChild(newRow);
    updateTotal();
}

// Eliminar fila de producto
function removeProductRow(button) {
    const container = document.getElementById('productsContainer');
    const rows = container.querySelectorAll('.product-row');

    if (rows.length > 1) {
        button.closest('.product-row').remove();
        updateTotal();
    } else {
        alert('Debe mantener al menos una fila de producto');
    }
}

// Actualizar info del producto seleccionado
function updateProductInfo(selectElement) {
    const row = selectElement.closest('.product-row');
    const priceDisplay = row.querySelector('.price-display');
    const quantityInput = row.querySelector('.quantity-input');

    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
    const stock = parseInt(selectedOption.getAttribute('data-stock')) || 0;

    priceDisplay.value = formatCurrency(price);
    quantityInput.max = stock;
    quantityInput.placeholder = stock > 0 ? `Máx: ${stock}` : 'Sin stock';

    quantityInput.disabled = stock === 0;
    if (stock === 0) quantityInput.value = '';

    updateTotal();
}

// Calcular y actualizar resumen y total
function updateTotal() {
    let total = 0;
    const rows = document.querySelectorAll('.product-row');
    const summaryContainer = document.getElementById('orderSummary');
    let summaryHTML = '';

    rows.forEach(row => {
        const select = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');

        if (select.value && quantityInput.value) {
            const selectedOption = select.options[select.selectedIndex];
            const price = parseFloat(selectedOption.getAttribute('data-price') || 0);
            const quantity = parseInt(quantityInput.value) || 0;
            const subtotal = price * quantity;
            total += subtotal;

            if (selectedOption.text && quantity > 0) {
                summaryHTML += `
                    <div class="flex flex-between mb-2">
                        <span>${selectedOption.text.split(' - ')[0]} (${quantity})</span>
                        <span class="fw-bold">${formatCurrency(subtotal)}</span>
                    </div>
                `;
            }
        }
    });

    document.getElementById('totalAmount').textContent = formatCurrency(total);

    if (summaryHTML) {
        summaryHTML += `
            <hr>
            <div class="flex flex-between">
                <span class="fw-bold">Total:</span>
                <span class="fw-bold text-primary">${formatCurrency(total)}</span>
            </div>
        `;
        summaryContainer.innerHTML = summaryHTML;
    } else {
        summaryContainer.innerHTML = '<p class="text-secondary text-center">Agrega productos para ver el resumen</p>';
    }
}

// Validación del formulario antes de enviar
document.getElementById('orderForm').addEventListener('submit', function(e) {
    const customerSelect = document.getElementById('customer_number');
    const productRows = document.querySelectorAll('.product-row');
    let hasValidProducts = false;

    // Validar cliente
    if (!customerSelect.value) {
        e.preventDefault();
        alert('Por favor selecciona un cliente');
        customerSelect.focus();
        return;
    }

    // Validar productos y cantidades
    for (let row of productRows) {
        const select = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');

        if (select.value && quantityInput.value && parseInt(quantityInput.value) > 0) {
            hasValidProducts = true;

            const availableStock = parseInt(select.options[select.selectedIndex].getAttribute('data-stock'));
            const requestedQuantity = parseInt(quantityInput.value);

            if (requestedQuantity > availableStock) {
                e.preventDefault();
                alert(`La cantidad solicitada (${requestedQuantity}) supera el stock disponible (${availableStock}) para el producto ${select.options[select.selectedIndex].text}`);
                quantityInput.focus();
                return;
            }
        }
    }

    if (!hasValidProducts) {
        e.preventDefault();
        alert('Debe agregar al menos un producto válido con cantidad mayor a 0');
        return;
    }

    // Debug: revisar datos antes de enviar
    console.log('Enviando productos:', Array.from(document.querySelectorAll('.product-select')).map(s => s.value));
    console.log('Cantidades:', Array.from(document.querySelectorAll('.quantity-input')).map(q => q.value));
});

// Función de formato de moneda
function formatCurrency(amount) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 2
    }).format(amount);
}
