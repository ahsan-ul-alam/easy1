import { initLocaleSwitch } from './locale-switch';
import { initLiveSearch } from './search';
import { initServerToasts, localizeDigits, request, setLoading, t, toast } from './ui';

const MAX_IMAGE_BYTES = 5 * 1024 * 1024;
const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

function initSidebar() {
    const sidebar = document.querySelector('[data-sidebar]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');

    const setOpen = (isOpen) => {
        sidebar.classList.toggle('hidden', !isOpen);
        sidebar.classList.toggle('flex', isOpen);
        backdrop.hidden = !isOpen;
    };

    document.querySelector('[data-sidebar-open]')?.addEventListener('click', () => setOpen(true));
    document.querySelector('[data-sidebar-close]')?.addEventListener('click', () => setOpen(false));
    backdrop?.addEventListener('click', () => setOpen(false));
}

/**
 * Show or hide a product from the product list without reloading.
 */
function initStatusToggles() {
    document.addEventListener('change', async (event) => {
        const toggle = event.target.closest('[data-status-toggle]');

        if (!toggle) {
            return;
        }

        const isActive = toggle.checked;
        const label = toggle.closest('label')?.querySelector('[data-status-label]');
        toggle.disabled = true;

        try {
            const data = await request(toggle.dataset.url, { method: 'PATCH', body: { is_active: isActive } });
            toggle.checked = data.is_active;

            if (label) {
                label.textContent = data.is_active ? t('admin.visible') : t('admin.hidden');
            }

            toast(data.message);
        } catch (error) {
            toggle.checked = !isActive;
            toast(error.message, { type: 'error' });
        } finally {
            toggle.disabled = false;
        }
    });
}

/**
 * Delete products from the list with a confirmation, removing the row on success.
 */
function initDeleteForms() {
    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('[data-delete-form]');

        if (!form) {
            return;
        }

        event.preventDefault();

        if (!window.confirm(form.dataset.confirm || t('confirm'))) {
            return;
        }

        const button = form.querySelector('button[type="submit"]');
        const row = form.closest('[data-product-row]');
        setLoading(button, true);

        try {
            const data = await request(form.action, { method: 'DELETE' });
            row.style.transition = 'opacity 200ms';
            row.style.opacity = '0';
            setTimeout(() => row.remove(), 200);
            toast(data.message);
        } catch (error) {
            setLoading(button, false);
            toast(error.message, { type: 'error' });
        }
    });
}

/**
 * Image picker for the product form: previews new uploads, lets them be removed before saving,
 * and keeps the total within the allowed number of images.
 */
function initImageManager() {
    const manager = document.querySelector('[data-image-manager]');

    if (!manager) {
        return;
    }

    const max = Number(manager.dataset.max);
    const input = manager.querySelector('[data-image-input]');
    const grid = manager.querySelector('[data-image-grid]');
    const drop = manager.querySelector('[data-image-drop]');
    const counter = manager.querySelector('[data-image-count]');
    const canEditFileList = typeof DataTransfer !== 'undefined';
    let pending = [];

    const keptExisting = () => [...manager.querySelectorAll('[data-remove-image]')].filter((checkbox) => !checkbox.checked).length;

    const refresh = () => {
        const total = keptExisting() + pending.length;
        counter.textContent = localizeDigits(`${total} / ${max}`);
        drop.hidden = total >= max;

        manager.querySelectorAll('[data-existing-image]').forEach((tile) => {
            const isRemoved = tile.querySelector('[data-remove-image]').checked;
            tile.querySelector('img').classList.toggle('opacity-40', isRemoved);
            tile.querySelector('[data-remove-label]').textContent = isRemoved ? t('admin.undo') : t('admin.remove');
        });
    };

    const syncInput = () => {
        if (!canEditFileList) {
            return;
        }

        const transfer = new DataTransfer();
        pending.forEach(({ file }) => transfer.items.add(file));
        input.files = transfer.files;
    };

    const addPreview = (entry) => {
        const tile = document.createElement('div');
        tile.className = 'relative aspect-square overflow-hidden rounded-xl bg-stone-100 ring-2 ring-emerald-500/60';

        const image = document.createElement('img');
        image.src = entry.url;
        image.alt = entry.file.name;
        image.className = 'size-full object-cover';

        const badge = document.createElement('span');
        badge.className = 'absolute top-2 left-2 rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-semibold text-white';
        badge.textContent = t('admin.new');

        tile.append(image, badge);

        if (canEditFileList) {
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'absolute inset-x-2 bottom-2 rounded-full bg-white/95 px-2 py-1.5 text-xs font-semibold text-stone-700 shadow-sm hover:text-red-600';
            remove.textContent = t('admin.remove');
            remove.addEventListener('click', () => {
                pending = pending.filter((candidate) => candidate !== entry);
                URL.revokeObjectURL(entry.url);
                tile.remove();
                syncInput();
                refresh();
            });
            tile.append(remove);
        }

        entry.tile = tile;
        grid.insertBefore(tile, drop);
    };

    input.addEventListener('change', () => {
        const chosen = [...input.files];

        if (!canEditFileList) {
            pending.forEach((entry) => entry.tile.remove());
            pending = [];
        }

        for (const file of chosen) {
            if (pending.some((entry) => entry.file === file)) {
                continue;
            }

            if (!ALLOWED_IMAGE_TYPES.includes(file.type)) {
                toast(t('admin.image_type', { name: file.name }), { type: 'error' });
                continue;
            }

            if (file.size > MAX_IMAGE_BYTES) {
                toast(t('admin.image_size', { name: file.name }), { type: 'error' });
                continue;
            }

            if (keptExisting() + pending.length >= max) {
                toast(t('admin.images_max', { max: localizeDigits(max) }), { type: 'error' });
                break;
            }

            const entry = { file, url: URL.createObjectURL(file) };
            pending.push(entry);
            addPreview(entry);
        }

        syncInput();
        refresh();
    });

    manager.addEventListener('change', (event) => {
        if (!event.target.matches('[data-remove-image]')) {
            return;
        }

        if (!event.target.checked && keptExisting() + pending.length > max) {
            event.target.checked = true;
            toast(t('admin.remove_new_first', { max: localizeDigits(max) }), { type: 'error' });
        }

        refresh();
    });

    refresh();
}

function initLogoPreview() {
    const input = document.querySelector('[data-logo-input]');

    input?.addEventListener('change', () => {
        const file = input.files[0];
        const preview = document.querySelector('[data-logo-preview]');

        if (!file) {
            return;
        }

        if (!ALLOWED_IMAGE_TYPES.includes(file.type) || file.size > 2 * 1024 * 1024) {
            toast(t('admin.logo_invalid'), { type: 'error' });
            input.value = '';
            return;
        }

        preview.src = URL.createObjectURL(file);
        preview.hidden = false;
        document.querySelector('[data-logo-placeholder]')?.setAttribute('hidden', '');
    });
}

/**
 * Ask for confirmation before submitting forms marked [data-confirm-submit], e.g. deleting a product.
 */
function initConfirmSubmit() {
    document.addEventListener('submit', (event) => {
        const form = event.target.closest('[data-confirm-submit]');

        if (form && !window.confirm(form.dataset.confirmSubmit || t('confirm'))) {
            event.preventDefault();
        }
    });
}

/**
 * Show a spinner on submit buttons of long-running forms (image uploads) and prevent double submits.
 */
function initSubmitLoading() {
    document.querySelectorAll('[data-submit-loading]').forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('[data-submit-button]');
            form.querySelector('[data-submit-spinner]')?.removeAttribute('hidden');
            setTimeout(() => setLoading(button, true));
        });
    });
}

initServerToasts();
initLocaleSwitch();
initSidebar();
initConfirmSubmit();
initLiveSearch();
initStatusToggles();
initDeleteForms();
initImageManager();
initLogoPreview();
initSubmitLoading();
