/**
 * Guest Action Gate
 *
 * Every account-required action (compare, report, collection, viewing
 * requests, formal offers, maintenance, ...) is intercepted for
 * unauthenticated visitors and sent to the account-create page with a
 * ?redirect= back to the page they were on.
 *
 * Mark elements with `data-auth-gate` (buttons, links) or `data-auth-gate`
 * / `.compare-toggle-form` on forms. Server-side routes stay open so
 * existing flows and feature tests are unaffected — this is UI-level
 * gating only.
 */

function isGuest() {
    return window.GTM_IS_GUEST === true;
}

function redirectToRegister() {
    const destination = new URL(window.GTM_REGISTER_URL || '/register', window.location.origin);
    destination.searchParams.set('redirect', window.location.pathname + window.location.search);
    window.location.assign(destination.toString());
}

/**
 * @param {EventTarget|null} target
 * @returns {Element|null}
 */
function gateTarget(target) {
    if (!target || typeof target.closest !== 'function') {
        return null;
    }

    return target.closest('[data-auth-gate], .compare-toggle-form');
}

export function initGuestGate() {
    if (document.__guestGateBound) {
        return;
    }
    document.__guestGateBound = true;

    // Capture phase so we beat inline onclick handlers and the per-form
    // AJAX listeners bound by compare-matrix / property-interactions.
    document.addEventListener('click', (e) => {
        if (!isGuest()) {
            return;
        }

        const target = gateTarget(e.target);
        if (!target) {
            return;
        }

        if (target.tagName === 'FORM') {
            // Inside a gated form: only intercept submitter clicks so form
            // controls (selects, inputs) stay usable before submitting.
            if (!e.target.closest('button, input[type="submit"]')) {
                return;
            }
        }

        e.preventDefault();
        e.stopPropagation();
        redirectToRegister();
    }, true);

    // Covers keyboard submits (Enter in a text field) that never fire a click.
    document.addEventListener('submit', (e) => {
        if (!isGuest()) {
            return;
        }

        if (!gateTarget(e.target)) {
            return;
        }

        e.preventDefault();
        e.stopPropagation();
        redirectToRegister();
    }, true);
}
