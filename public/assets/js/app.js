/**
 * Sport Shop – Main JavaScript
 *
 * Provides: CSRF helper, showToast(), updateCartBadge(), cart badge on load.
 */

(function () {
    'use strict';

    // ─── CSRF HELPER ─────────────────────────────────────────────────
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    /**
     * Wrapper around fetch that auto-adds CSRF token + JSON headers.
     */
    window.apiFetch = function (url, options = {}) {
        const defaults = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            credentials: 'same-origin',
        };

        if (options.headers) {
            options.headers = { ...defaults.headers, ...options.headers };
        }

        return fetch(url, { ...defaults, ...options });
    };

    // ─── TOAST NOTIFICATIONS ─────────────────────────────────────────
    /**
     * Show a toast notification.
     * @param {string} message  Text content
     * @param {'success'|'error'} type  Toast type (default 'success')
     * @param {number} duration  Auto-dismiss in ms (default 4000)
     */
    window.showToast = function (message, type = 'success', duration = 4000) {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = 'toast toast-' + type;
        toast.innerHTML =
            '<span>' + escapeHtml(message) + '</span>' +
            '<button class="toast-close" onclick="this.parentElement.remove()">✕</button>';
        container.appendChild(toast);

        setTimeout(function () {
            if (toast.parentElement) toast.remove();
        }, duration);
    };

    // ─── CART BADGE ──────────────────────────────────────────────────
    /**
     * Update the cart badge count in the nav.
     * @param {number} count  Number of items in the cart.
     */
    window.updateCartBadge = function (count) {
        const badge = document.getElementById('cart-count');
        if (!badge) return;

        count = parseInt(count, 10) || 0;
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    };

    /**
     * Fetch cart and update badge count on page load.
     */
    function loadCartBadge() {
        apiFetch('/api/cart')
            .then(function (res) { return res.json(); })
            .then(function (json) {
                if (json.success && json.data && json.data.items) {
                    var total = 0;
                    json.data.items.forEach(function (item) {
                        total += parseInt(item.quantity, 10) || 0;
                    });
                    updateCartBadge(total);
                }
            })
            .catch(function () { /* silently ignore */ });
    }

    // ─── FORMAT VND ──────────────────────────────────────────────────
    /**
     * Format a number as VND currency string.
     * @param {number|string} amount
     * @returns {string} e.g. "1.250.000 ₫"
     */
    window.formatVND = function (amount) {
        var num = parseInt(amount, 10) || 0;
        return num.toLocaleString('vi-VN') + ' ₫';
    };

    // ─── HTML ESCAPE ─────────────────────────────────────────────────
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    // ─── INIT ────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        loadCartBadge();
    });
})();

