// Conditional polyfill loading for Baseline/Limited web platform features
if (!Object.hasOwn(HTMLElement.prototype, 'popover')) {
    import('@oddbird/popover-polyfill');
}

if (!Object.hasOwn(HTMLElement.prototype, 'focusGroup')) {
    import('@microsoft/focusgroup-polyfill').then(({ polyfill }) => {
        if (typeof polyfill === 'function') {
            polyfill();
        }
    });
}

function initGtaviPlatformEnhancements() {
    // 1. Invoker Commands fallback for browsers without command / commandfor support
    if (!('commandForElement' in HTMLButtonElement.prototype) && !document.documentElement.dataset.commandFallbackBound) {
        document.documentElement.dataset.commandFallbackBound = 'true';
        document.addEventListener('click', (event) => {
            const trigger = event.target instanceof Element ? event.target.closest('button[command][commandfor]') : null;
            if (!trigger) {
                return;
            }
            const targetId = trigger.getAttribute('commandfor');
            const command = trigger.getAttribute('command');
            const target = targetId ? document.getElementById(targetId) : null;
            if (!(target instanceof HTMLDialogElement)) {
                return;
            }
            if (command === 'show-modal' && !target.open) {
                target.showModal();
            } else if (command === 'close' && target.open) {
                target.close();
            }
        });
    }

    // 2. Light-dismiss fallback for <dialog closedby="any"> in browsers lacking closedBy support
    if (!('closedBy' in HTMLDialogElement.prototype)) {
        const outside = (e, dialog) => {
            const r = dialog.getBoundingClientRect();
            return e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom;
        };

        document.querySelectorAll('dialog[closedby="any"]').forEach((dialog) => {
            if (dialog.dataset.lightDismissBound === 'true') {
                return;
            }
            dialog.dataset.lightDismissBound = 'true';

            let pressedOutside = false;
            dialog.addEventListener('pointerdown', (e) => {
                pressedOutside = outside(e, dialog);
            });
            dialog.addEventListener('pointerup', (e) => {
                if (pressedOutside && outside(e, dialog)) {
                    dialog.close();
                }
            });
        });
    }

    // 3. Sync tab selection when find-in-page triggers beforematch on hidden="until-found" tabpanels
    document.querySelectorAll('[role="tabpanel"]').forEach((panel) => {
        if (panel.dataset.beforematchBound === 'true') {
            return;
        }
        panel.dataset.beforematchBound = 'true';

        panel.addEventListener('beforematch', () => {
            const tabId = panel.getAttribute('aria-labelledby');
            if (!tabId) {
                return;
            }
            const matchingTab = document.getElementById(tabId);
            if (matchingTab) {
                matchingTab.click();
            }
        });
    });

    // 4. Reset dialog.returnValue on close so stale button values never leak across opens
    document.querySelectorAll('dialog').forEach((dialog) => {
        if (dialog.dataset.closeResetBound === 'true') {
            return;
        }
        dialog.dataset.closeResetBound = 'true';
        dialog.addEventListener('close', () => {
            dialog.returnValue = '';
        });
    });

    // 5. Delegated confirmation handler for forms carrying data-confirm
    if (!document.documentElement.dataset.confirmFallbackBound) {
        document.documentElement.dataset.confirmFallbackBound = 'true';
        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) {
                return;
            }
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    }

    // 6. Accessible show/hide password toggle on sign-in form
    const togglePasswordButton = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('password');
    if (
        togglePasswordButton instanceof HTMLButtonElement
        && passwordInput instanceof HTMLInputElement
        && togglePasswordButton.dataset.togglePasswordBound !== 'true'
    ) {
        togglePasswordButton.dataset.togglePasswordBound = 'true';
        togglePasswordButton.addEventListener('click', () => {
            const isMasked = passwordInput.type === 'password';
            passwordInput.type = isMasked ? 'text' : 'password';
            togglePasswordButton.setAttribute('aria-pressed', isMasked ? 'true' : 'false');
            togglePasswordButton.setAttribute('aria-label', isMasked ? 'Hide password' : 'Show password');
            togglePasswordButton.textContent = isMasked ? 'Hide' : 'Show';
        });
    }
}

document.addEventListener('DOMContentLoaded', initGtaviPlatformEnhancements);
document.addEventListener('livewire:navigated', initGtaviPlatformEnhancements);
