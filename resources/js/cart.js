/**
 * Guest cart stored in localStorage as [{ id, quantity }].
 * Only IDs and quantities are stored; names and prices always come from the server.
 */

import { localizeDigits } from './ui';

const STORAGE_KEY = 'shop.cart.v1';
export const MAX_QUANTITY = 99;
export const MAX_LINES = 50;

let memoryItems = [];

export const clampQuantity = (value) => Math.min(MAX_QUANTITY, Math.max(1, Number.parseInt(value, 10) || 1));

function normalize(items) {
    if (!Array.isArray(items)) {
        return [];
    }

    const merged = new Map();

    for (const item of items) {
        const id = Number.parseInt(item?.id, 10);

        if (id > 0) {
            merged.set(id, clampQuantity((merged.get(id) ?? 0) + (Number.parseInt(item.quantity, 10) || 1)));
        }
    }

    return [...merged].slice(0, MAX_LINES).map(([id, quantity]) => ({ id, quantity }));
}

function read() {
    try {
        return normalize(JSON.parse(window.localStorage.getItem(STORAGE_KEY) ?? '[]'));
    } catch {
        return normalize(memoryItems);
    }
}

function write(items) {
    memoryItems = items;

    try {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
    } catch {
        // Storage unavailable (e.g. private mode); keep the in-memory copy for this page.
    }

    document.dispatchEvent(new CustomEvent('cart:changed', { detail: { items } }));
}

export const cart = {
    items: () => read(),

    count: () => read().reduce((total, item) => total + item.quantity, 0),

    /**
     * Add a product. Returns false when the cart already holds the maximum number of different products.
     */
    add(id, quantity = 1) {
        const items = read();
        const existing = items.find((item) => item.id === Number(id));

        if (existing) {
            existing.quantity = clampQuantity(existing.quantity + quantity);
        } else if (items.length >= MAX_LINES) {
            return false;
        } else {
            items.push({ id: Number(id), quantity: clampQuantity(quantity) });
        }

        write(items);

        return true;
    },

    setQuantity(id, quantity) {
        write(read().map((item) => (item.id === Number(id) ? { ...item, quantity: clampQuantity(quantity) } : item)));
    },

    remove(id) {
        this.removeMany([id]);
    },

    removeMany(ids) {
        const removed = new Set(ids.map(Number));
        write(read().filter((item) => !removed.has(item.id)));
    },

    clear() {
        write([]);
    },
};

export function updateCartBadges() {
    const count = cart.count();

    document.querySelectorAll('[data-cart-count]').forEach((badge) => {
        badge.textContent = localizeDigits(count > 99 ? '99+' : count);
        badge.hidden = count === 0;
    });
}

document.addEventListener('cart:changed', updateCartBadges);

window.addEventListener('storage', (event) => {
    if (event.key === STORAGE_KEY) {
        updateCartBadges();
        document.dispatchEvent(new CustomEvent('cart:external-change'));
    }
});
