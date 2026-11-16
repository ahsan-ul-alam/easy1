/**
 * Language switcher: reloads the current page (including live-search state) in the chosen language,
 * and carries unsaved values of forms marked [data-preserve-form] across the reload.
 * File and password inputs are never stored.
 */

const STORAGE_PREFIX = 'locale-switch:';
const SKIPPED_TYPES = ['file', 'password', 'hidden', 'submit', 'button', 'reset'];

const storageKey = () => STORAGE_PREFIX + window.location.pathname;

const preservableFields = (form) => [...form.elements].filter((field) => field.name && !SKIPPED_TYPES.includes(field.type));

function saveForms() {
    const forms = [...document.querySelectorAll('form[data-preserve-form]')].map((form) => ({
        key: form.dataset.preserveForm,
        fields: preservableFields(form).map((field) => (field.type === 'checkbox' || field.type === 'radio' ? field.checked : field.value)),
    }));

    if (forms.length === 0) {
        return;
    }

    try {
        window.sessionStorage.setItem(storageKey(), JSON.stringify(forms));
    } catch {
        // Storage unavailable; the page still switches language, only unsaved input is lost.
    }
}

function restoreForms() {
    let forms;

    try {
        forms = JSON.parse(window.sessionStorage.getItem(storageKey()) || 'null');
        window.sessionStorage.removeItem(storageKey());
    } catch {
        return;
    }

    (forms || []).forEach(({ key, fields }) => {
        const form = document.querySelector(`form[data-preserve-form="${CSS.escape(key)}"]`);
        const current = form ? preservableFields(form) : [];

        // Only restore into the same form layout it was saved from.
        if (current.length !== fields.length) {
            return;
        }

        current.forEach((field, index) => {
            if (field.type === 'checkbox' || field.type === 'radio') {
                field.checked = Boolean(fields[index]);
            } else {
                field.value = fields[index];
            }
        });
    });
}

export function initLocaleSwitch() {
    restoreForms();

    document.addEventListener('click', (event) => {
        const link = event.target.closest('[data-locale-switch]');

        if (!link || event.metaKey || event.ctrlKey || event.shiftKey) {
            return;
        }

        event.preventDefault();
        saveForms();

        const url = new URL(window.location.href);
        url.searchParams.set('lang', link.dataset.localeSwitch);
        window.location.assign(url.toString());
    });
}
