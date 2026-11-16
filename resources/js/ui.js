/**
 * Small UI helpers shared by the storefront and the admin panel.
 */

export const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const i18n = (() => {
    try {
        return JSON.parse(document.getElementById('i18n')?.textContent || '{}');
    } catch {
        return {};
    }
})();

export const locale = i18n.locale || document.documentElement.lang || 'bn';

const BENGALI_DIGITS = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

/**
 * Show digits in the page language (Bengali digits on Bengali pages).
 */
export const localizeDigits = (value) => (locale === 'bn' ? String(value).replace(/[0-9]/g, (digit) => BENGALI_DIGITS[digit]) : String(value));

export const formatMoney = (amount) => '৳' + localizeDigits(Number(amount || 0).toLocaleString('en-US'));

/**
 * Translate a key from lang/{locale}/js.php, e.g. t('cart.added', { name: 'Hoodie' }).
 */
export function t(key, replacements = {}) {
    let message = key.split('.').reduce((group, part) => group?.[part], i18n.messages);

    if (typeof message !== 'string') {
        return key;
    }

    for (const [name, value] of Object.entries(replacements)) {
        message = message.replaceAll(`:${name}`, value);
    }

    return message;
}

export function debounce(callback, wait = 300) {
    let timer;

    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => callback(...args), wait);
    };
}

/**
 * fetch() wrapper that sends Laravel's CSRF token and AJAX headers and parses JSON responses.
 * Rejects with an Error whose message is suitable to show to the user.
 */
export async function request(url, { method = 'GET', body, signal } = {}) {
    const headers = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken(),
    };

    if (body !== undefined && !(body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
        body = JSON.stringify(body);
    }

    const response = await fetch(url, { method, headers, body, signal, credentials: 'same-origin' });

    if (response.status === 419) {
        throw new Error(t('session_expired'));
    }

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        const firstError = data.errors ? Object.values(data.errors)[0]?.[0] : null;
        throw new Error(firstError || data.message || t('generic_error'));
    }

    return data;
}

const TOAST_STYLES = {
    success: 'bg-stone-900 text-white',
    error: 'bg-red-600 text-white',
};

function dismissLater(toast, delay = 4000) {
    setTimeout(() => {
        toast.style.transition = 'opacity 200ms, transform 200ms';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(4px)';
        setTimeout(() => toast.remove(), 220);
    }, delay);
}

/**
 * Show a short notification. An optional action renders as a link, e.g. "View cart".
 */
export function toast(message, { type = 'success', action } = {}) {
    const container = document.querySelector('[data-toasts]');

    if (!container) {
        return;
    }

    const element = document.createElement('div');
    element.className = `pointer-events-auto flex w-full max-w-sm items-center gap-3 rounded-2xl px-4 py-3 text-sm shadow-lift ${TOAST_STYLES[type] ?? TOAST_STYLES.success}`;
    element.setAttribute('role', type === 'error' ? 'alert' : 'status');

    const text = document.createElement('p');
    text.className = 'flex-1';
    text.textContent = message;
    element.append(text);

    if (action) {
        const link = document.createElement('a');
        link.href = action.href;
        link.textContent = action.label;
        link.className = 'shrink-0 rounded-full bg-white/15 px-3 py-1.5 text-xs font-semibold hover:bg-white/25';
        element.append(link);
    }

    container.append(element);
    dismissLater(element);
}

export function initServerToasts() {
    document.querySelectorAll('[data-toast]').forEach((element) => dismissLater(element, 5000));
}

export function setLoading(button, isLoading) {
    if (!button) {
        return;
    }

    button.disabled = isLoading;
    button.setAttribute('aria-busy', isLoading ? 'true' : 'false');
    button.classList.toggle('opacity-60', isLoading);
}
