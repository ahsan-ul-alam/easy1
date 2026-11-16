import { cart, clampQuantity, updateCartBadges } from './cart';
import { initLocaleSwitch } from './locale-switch';
import { initLiveSearch } from './search';
import { debounce, formatMoney, initServerToasts, localizeDigits, request, t, toast } from './ui';

function initMobileMenu() {
    const toggle = document.querySelector('[data-menu-toggle]');
    const menu = document.getElementById('mobile-menu');

    toggle?.addEventListener('click', () => {
        const isOpen = !menu.hidden;
        menu.hidden = isOpen;
        toggle.setAttribute('aria-expanded', String(!isOpen));
    });
}

/**
 * Plus/minus steppers around a number input. Changes are announced with "change" events.
 */
function initQuantitySteppers() {
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-quantity-step]');

        if (!button) {
            return;
        }

        const input = button.closest('[data-quantity]')?.querySelector('[data-quantity-input]');

        if (input) {
            input.value = clampQuantity(Number(input.value) + Number(button.dataset.quantityStep));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });

    document.addEventListener('change', (event) => {
        if (event.target.matches('[data-quantity-input]')) {
            event.target.value = clampQuantity(event.target.value);
        }
    });
}

function initAddToCart() {
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-add-to-cart]');

        if (!button) {
            return;
        }

        event.preventDefault();

        const source = button.dataset.quantitySource ? document.querySelector(button.dataset.quantitySource) : null;
        const quantity = clampQuantity(source?.value ?? 1);

        if (!cart.add(button.dataset.productId, quantity)) {
            toast(t('cart.full'), { type: 'error' });
            return;
        }

        button.classList.add('scale-95');
        setTimeout(() => button.classList.remove('scale-95'), 150);

        toast(t('cart.added', { name: button.dataset.productName }), {
            action: { href: document.body.dataset.cartUrl, label: t('cart.view') },
        });
    });
}

/**
 * Product page: keep the line total and the WhatsApp order link in step with the chosen quantity.
 */
function initProductQuantity() {
    const input = document.querySelector('#quantity[data-quantity-input]');

    if (!input) {
        return;
    }

    const total = document.querySelector('[data-line-total]');
    const orderLink = document.querySelector('[data-order-link]');

    const update = () => {
        const quantity = clampQuantity(input.value);

        if (total) {
            total.textContent = formatMoney(Number(total.dataset.unitPrice) * quantity);
        }

        if (orderLink) {
            const url = new URL(orderLink.href);
            url.searchParams.set('quantity', quantity);
            orderLink.href = url.toString();
        }
    };

    input.addEventListener('input', update);
    input.addEventListener('change', update);
}

function initGallery() {
    document.querySelectorAll('[data-gallery]').forEach((gallery) => {
        const main = gallery.querySelector('[data-gallery-main]');
        const thumbs = gallery.querySelectorAll('[data-gallery-thumb]');

        thumbs.forEach((thumb) => {
            thumb.addEventListener('click', () => {
                main.removeAttribute('srcset');
                main.src = thumb.dataset.src;

                thumbs.forEach((other) => {
                    const isActive = other === thumb;
                    other.setAttribute('aria-pressed', String(isActive));
                    other.classList.toggle('ring-stone-900', isActive);
                    other.classList.toggle('ring-transparent', !isActive);
                });
            });
        });
    });
}

/**
 * Cart page: renders items with current server prices and keeps totals updated as quantities change.
 */
function initCartPage() {
    const root = document.querySelector('[data-cart-page]');

    if (!root) {
        return;
    }

    const elements = {
        loading: root.querySelector('[data-cart-loading]'),
        empty: root.querySelector('[data-cart-empty]'),
        content: root.querySelector('[data-cart-content]'),
        list: root.querySelector('[data-cart-items]'),
        template: root.querySelector('[data-cart-item-template]'),
        notice: root.querySelector('[data-cart-notice]'),
        totalItems: root.querySelector('[data-cart-total-items]'),
        grandTotal: root.querySelector('[data-cart-grand-total]'),
        orderForm: root.querySelector('[data-cart-order-form]'),
        orderInputs: root.querySelector('[data-cart-order-inputs]'),
        clear: root.querySelector('[data-cart-clear]'),
    };

    const prices = new Map();
    let controller;

    const showState = (state) => {
        elements.loading.hidden = state !== 'loading';
        elements.empty.hidden = state !== 'empty';
        elements.content.hidden = state !== 'content';
    };

    const showNotice = (message) => {
        elements.notice.textContent = message;
        elements.notice.hidden = !message;
    };

    const renderTotals = () => {
        const items = cart.items().filter((item) => prices.has(item.id));
        const totalItems = items.reduce((sum, item) => sum + item.quantity, 0);
        const grandTotal = items.reduce((sum, item) => sum + item.quantity * prices.get(item.id), 0);

        elements.totalItems.textContent = localizeDigits(totalItems);
        elements.grandTotal.textContent = formatMoney(grandTotal);

        elements.list.querySelectorAll('[data-cart-item]').forEach((row) => {
            const item = items.find((candidate) => candidate.id === Number(row.dataset.id));

            if (item) {
                row.querySelector('[data-item-subtotal]').textContent = formatMoney(item.quantity * prices.get(item.id));
            }
        });

        if (items.length === 0) {
            showState('empty');
        }
    };

    const createRow = (item) => {
        const row = elements.template.content.firstElementChild.cloneNode(true);
        row.dataset.id = item.id;
        row.querySelectorAll('[data-item-link]').forEach((link) => (link.href = item.url));

        const image = row.querySelector('[data-item-image]');

        if (item.image) {
            image.src = item.image;
            image.alt = item.name;
        } else {
            image.remove();
        }

        row.querySelector('[data-item-remove]').setAttribute('aria-label', t('cart.remove_item', { name: item.name }));

        return row;
    };

    const renderItems = (items) => {
        const rows = new Map([...elements.list.querySelectorAll('[data-cart-item]')].map((row) => [Number(row.dataset.id), row]));

        items.forEach((item) => {
            const row = rows.get(item.id) ?? createRow(item);
            rows.delete(item.id);

            row.querySelector('[data-item-name]').textContent = item.name;
            row.querySelector('[data-item-price]').textContent = t('cart.price_each', { price: item.price_label });
            row.querySelector('[data-item-subtotal]').textContent = item.subtotal_label;

            const input = row.querySelector('[data-quantity-input]');

            if (document.activeElement !== input) {
                input.value = item.quantity;
            }

            elements.list.append(row);
        });

        rows.forEach((row) => row.remove());
    };

    const sync = async () => {
        const items = cart.items();

        if (items.length === 0) {
            showState('empty');
            return;
        }

        controller?.abort();
        controller = new AbortController();

        try {
            const data = await request(document.body.dataset.cartSyncUrl, {
                method: 'POST',
                body: { items },
                signal: controller.signal,
            });

            if (data.unavailable.length > 0) {
                cart.removeMany(data.unavailable);
                showNotice(t('cart.items_removed'));
            }

            prices.clear();
            data.items.forEach((item) => prices.set(item.id, item.price));

            if (data.items.length === 0) {
                showState('empty');
                return;
            }

            renderItems(data.items);
            elements.totalItems.textContent = data.total_items_label;
            elements.grandTotal.textContent = data.grand_total_label;
            showState('content');
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            showState(prices.size > 0 ? 'content' : 'loading');
            showNotice(t('cart.prices_failed'));
            toast(error.message, { type: 'error' });
        }
    };

    const syncSoon = debounce(sync, 500);

    elements.list.addEventListener('change', (event) => {
        if (!event.target.matches('[data-quantity-input]')) {
            return;
        }

        const row = event.target.closest('[data-cart-item]');
        cart.setQuantity(row.dataset.id, event.target.value);
        renderTotals();
        syncSoon();
    });

    elements.list.addEventListener('click', (event) => {
        const button = event.target.closest('[data-item-remove]');

        if (!button) {
            return;
        }

        const row = button.closest('[data-cart-item]');
        cart.remove(row.dataset.id);
        prices.delete(Number(row.dataset.id));
        row.style.transition = 'opacity 150ms';
        row.style.opacity = '0';
        setTimeout(() => {
            row.remove();
            renderTotals();
        }, 150);
    });

    elements.clear?.addEventListener('click', () => {
        if (window.confirm(t('cart.clear_confirm'))) {
            cart.clear();
            prices.clear();
            elements.list.replaceChildren();
            showNotice('');
            showState('empty');
        }
    });

    elements.orderForm?.addEventListener('submit', (event) => {
        // Send the whole browser cart; the server revalidates every product and price.
        const items = cart.items();

        if (items.length === 0) {
            event.preventDefault();
            toast(t('cart.empty'), { type: 'error' });
            return;
        }

        elements.orderInputs.replaceChildren(
            ...items.flatMap((item, index) =>
                ['id', 'quantity'].map((field) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `items[${index}][${field}]`;
                    input.value = item[field];
                    return input;
                }),
            ),
        );
    });

    document.addEventListener('cart:external-change', sync);
    window.addEventListener('pageshow', (event) => event.persisted && sync());

    sync();
}

initServerToasts();
initLocaleSwitch();
updateCartBadges();
initMobileMenu();
initQuantitySteppers();
initAddToCart();
initProductQuantity();
initGallery();
initLiveSearch();
initCartPage();
