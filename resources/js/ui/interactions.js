// Native dialogs provide keyboard focus containment and Escape handling.
const sidebar = document.querySelector('#app-sidebar');
const navigation = document.querySelector('#navigation-dialog');
const navigationToggle = document.querySelector('[data-navigation-open]');

if (sidebar && navigation && navigationToggle && typeof navigation.showModal === 'function') {
    const shell = document.querySelector('[data-app-shell]');
    const workspace = document.querySelector('[data-app-workspace]');
    const desktop = window.matchMedia('(min-width: 1024px)');
    const placeNavigation = () => {
        if (navigation.open) navigation.close();
        if (desktop.matches) shell.insertBefore(sidebar, workspace);
        else navigation.append(sidebar);
        navigationToggle.setAttribute('aria-expanded', 'false');
    };
    placeNavigation();
    document.documentElement.classList.add('js');
    desktop.addEventListener('change', placeNavigation);
    navigationToggle.addEventListener('click', () => {
        navigation.showModal();
        navigationToggle.setAttribute('aria-expanded', 'true');
    });
    document.querySelector('[data-navigation-close]')?.addEventListener('click', () => navigation.close());
    navigation.addEventListener('close', () => navigationToggle.setAttribute('aria-expanded', 'false'));
    navigation.addEventListener('click', (event) => {
        if (event.target !== navigation) return;
        const rect = navigation.getBoundingClientRect();
        if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) navigation.close();
    });
}

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-print]')) window.print();
    const close = event.target.closest('[data-dialog-close]');
    if (close) close.closest('dialog')?.close();
});

document.addEventListener('invalid', (event) => {
    const field = event.target;
    if (field.dataset.requiredMessage && field.validity.valueMissing) {
        field.setCustomValidity(field.dataset.requiredMessage);
    }
}, true);
for (const eventName of ['input', 'change']) {
    document.addEventListener(eventName, (event) => {
        if (event.target.matches('[data-required-message]')) event.target.setCustomValidity('');
    });
}

const confirmation = document.querySelector('#confirmation-dialog');
const confirmedForms = new WeakSet();
const pendingForms = new Map();
let pendingConfirmation = null;

confirmation?.addEventListener('close', () => { pendingConfirmation = null; });
confirmation?.querySelector('[data-confirmation-accept]')?.addEventListener('click', () => {
    if (!pendingConfirmation) return;
    const { form, submitter } = pendingConfirmation;
    pendingConfirmation = null;
    confirmation.close();
    confirmedForms.add(form);
    if (submitter) form.requestSubmit(submitter);
    else form.requestSubmit();
});

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || event.defaultPrevented) return;
    if (pendingForms.has(form)) {
        event.preventDefault();
        return;
    }
    if (form.dataset.confirmMessage && !confirmedForms.has(form)) {
        event.preventDefault();
        if (confirmation && typeof confirmation.showModal === 'function') {
            pendingConfirmation = { form, submitter: event.submitter };
            confirmation.querySelector('[data-confirmation-message]').textContent = form.dataset.confirmMessage;
            const accept = confirmation.querySelector('[data-confirmation-accept]');
            accept.querySelector('[data-button-label]').textContent = form.dataset.confirmLabel || 'Lanjutkan';
            accept.classList.toggle('ui-button--primary', form.dataset.confirmVariant === 'primary');
            accept.classList.toggle('ui-button--secondary', form.dataset.confirmVariant === 'secondary');
            accept.classList.toggle('ui-button--danger', !['primary', 'secondary'].includes(form.dataset.confirmVariant));
            confirmation.showModal();
        } else if (window.confirm(form.dataset.confirmMessage)) {
            confirmedForms.add(form);
            if (event.submitter) form.requestSubmit(event.submitter);
            else form.requestSubmit();
        }
        return;
    }
    confirmedForms.delete(form);
    if (!form.hasAttribute('data-submit-state')) return;
    // Defer until other submit handlers can cancel; preserve named submitter values.
    queueMicrotask(() => {
        if (event.defaultPrevented) return;
        const button = event.submitter;
        const label = button?.querySelector('[data-button-label]');
        const spinner = button?.querySelector('[data-button-spinner]');
        const status = form.querySelector('[data-submit-status]');
        pendingForms.set(form, { button, label, spinner, html: label?.innerHTML });
        form.setAttribute('aria-busy', 'true');
        if (button) button.setAttribute('aria-disabled', 'true');
        if (label) label.textContent = button.dataset.loadingText || 'Memproses…';
        if (spinner) spinner.hidden = false;
        if (status) status.textContent = button?.dataset.loadingText || 'Memproses…';
    });
});

// Restore controls when the browser returns to a cached page.
window.addEventListener('pageshow', () => {
    pendingForms.forEach(({ button, label, spinner, html }, form) => {
        form.removeAttribute('aria-busy');
        button?.removeAttribute('aria-disabled');
        if (label) label.innerHTML = html;
        if (spinner) spinner.hidden = true;
        const status = form.querySelector('[data-submit-status]');
        if (status) status.textContent = '';
    });
    pendingForms.clear();
});
