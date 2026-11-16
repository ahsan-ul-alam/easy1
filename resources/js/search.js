import { debounce, t, toast } from './ui';

/**
 * Live search for forms marked with [data-search-form].
 * The server returns only the results fragment for AJAX requests.
 */
export function initLiveSearch() {
    document.querySelectorAll('[data-search-form]').forEach((form) => {
        const target = document.querySelector(form.dataset.searchTarget);
        const spinner = form.querySelector('[data-search-spinner]');
        let controller;

        if (!target) {
            return;
        }

        const run = async () => {
            const url = new URL(form.action, window.location.origin);
            url.hash = '';
            url.search = new URLSearchParams(new FormData(form)).toString();

            for (const [key, value] of [...url.searchParams.entries()]) {
                if (value === '') {
                    url.searchParams.delete(key);
                }
            }

            controller?.abort();
            controller = new AbortController();
            spinner?.removeAttribute('hidden');
            target.setAttribute('aria-busy', 'true');
            target.style.opacity = '0.55';

            try {
                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
                    signal: controller.signal,
                });

                if (!response.ok) {
                    throw new Error();
                }

                target.innerHTML = await response.text();
                window.history.replaceState({}, '', url);
            } catch (error) {
                if (error.name !== 'AbortError') {
                    toast(t('search_failed'), { type: 'error' });
                }
            } finally {
                spinner?.setAttribute('hidden', '');
                target.removeAttribute('aria-busy');
                target.style.opacity = '';
            }
        };

        const debouncedRun = debounce(run, 300);

        form.addEventListener('input', debouncedRun);
        form.addEventListener('change', (event) => {
            if (event.target.tagName === 'SELECT') {
                run();
            }
        });
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            run();
        });
    });
}
