/**
 * Luxury Real Estate Multi-Currency Conversion Engine
 * Supports ETB (Base), USD, EUR, GBP, and AED with instant client-side switching,
 * localStorage persistence, formatted outputs, and reactive event broadcasting.
 */

export const CURRENCIES = {
    ETB: {
        code: 'ETB',
        symbol: 'ETB',
        name: 'Ethiopian Birr',
        rate: 1.0,
        flag: '🇪🇹',
        symbolPlacement: 'before-space',
    },
    USD: {
        code: 'USD',
        symbol: '$',
        name: 'US Dollar',
        rate: 0.008, // 1 USD ≈ 125 ETB
        flag: '🇺🇸',
        symbolPlacement: 'before',
    },
    EUR: {
        code: 'EUR',
        symbol: '€',
        name: 'Euro',
        rate: 0.0074, // 1 EUR ≈ 135 ETB
        flag: '🇪🇺',
        symbolPlacement: 'before',
    },
    GBP: {
        code: 'GBP',
        symbol: '£',
        name: 'British Pound',
        rate: 0.00625, // 1 GBP ≈ 160 ETB
        flag: '🇬🇧',
        symbolPlacement: 'before',
    },
    AED: {
        code: 'AED',
        symbol: 'AED',
        name: 'UAE Dirham',
        rate: 0.0294, // 1 AED ≈ 34 ETB
        flag: '🇦🇪',
        symbolPlacement: 'before-space',
    },
};

/**
 * Live rate overrides injected by the server as "foreign currency per 1 ETB".
 * Admins maintain the ETB-per-unit rates from the dashboard; the server
 * converts them to this shape before rendering window.GTM_CURRENCY_RATES.
 */
const RATE_OVERRIDES = (typeof window !== 'undefined' && window.GTM_CURRENCY_RATES) || {};

Object.entries(RATE_OVERRIDES).forEach(([code, rate]) => {
    const numericRate = parseFloat(rate);
    if (CURRENCIES[code] && numericRate > 0) {
        CURRENCIES[code].rate = numericRate;
    }
});

const STORAGE_KEY = 'gtm_preferred_currency';

export function getActiveCurrency() {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored && CURRENCIES[stored]) {
            return stored;
        }
    } catch (_) {}
    return 'ETB';
}

export function convertAmount(baseAmountInETB, targetCurrency = null) {
    const code = targetCurrency || getActiveCurrency();
    const config = CURRENCIES[code] || CURRENCIES.ETB;
    const numeric = parseFloat(baseAmountInETB) || 0;
    return numeric * config.rate;
}

export function formatPrice(baseAmountInETB, targetCurrency = null, options = {}) {
    const code = targetCurrency || getActiveCurrency();
    const config = CURRENCIES[code] || CURRENCIES.ETB;
    const converted = convertAmount(baseAmountInETB, code);

    const decimals = options.decimals !== undefined
        ? options.decimals
        : (code === 'ETB' ? 0 : (converted < 1000 ? 2 : 0));

    const formattedNumber = converted.toLocaleString(undefined, {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });

    if (config.symbolPlacement === 'before') {
        return `${config.symbol}${formattedNumber}`;
    }

    return `${config.symbol} ${formattedNumber}`;
}

export function setCurrency(code) {
    if (!CURRENCIES[code]) return;

    try {
        localStorage.setItem(STORAGE_KEY, code);
    } catch (_) {}

    updateAllCurrencyUI(code);

    window.dispatchEvent(new CustomEvent('currency-changed', {
        detail: {
            currency: code,
            config: CURRENCIES[code],
            format: (amount, opts) => formatPrice(amount, code, opts),
            convert: (amount) => convertAmount(amount, code),
        },
    }));
}

export function updateAllCurrencyUI(activeCode = null) {
    const code = activeCode || getActiveCurrency();
    const config = CURRENCIES[code] || CURRENCIES.ETB;

    // 1. Update all elements displaying property prices
    document.querySelectorAll('[data-base-price]').forEach((el) => {
        const base = parseFloat(el.getAttribute('data-base-price')) || 0;
        const suffix = el.getAttribute('data-price-suffix') || '';
        const prefix = el.getAttribute('data-price-prefix') || '';
        const formatted = formatPrice(base, code);
        el.textContent = `${prefix}${formatted}${suffix}`;
    });

    // 2. Update currency trigger badges in header and menus
    document.querySelectorAll('[data-currency-active-code]').forEach((el) => {
        el.textContent = config.code;
    });

    document.querySelectorAll('[data-currency-active-flag]').forEach((el) => {
        el.textContent = config.flag;
    });

    document.querySelectorAll('[data-currency-active-symbol]').forEach((el) => {
        el.textContent = config.symbol;
    });

    // 3. Highlight selected option in dropdowns
    document.querySelectorAll('[data-set-currency]').forEach((btn) => {
        const btnCode = btn.getAttribute('data-set-currency');
        if (btnCode === code) {
            btn.classList.add('bg-[#1d3c34]', 'text-white');
            btn.classList.remove('text-[#1d3c34]', 'hover:bg-[#f3eadc]');
        } else {
            btn.classList.remove('bg-[#1d3c34]', 'text-white');
            btn.classList.add('text-[#1d3c34]', 'hover:bg-[#f3eadc]');
        }
    });
}

let currencyEngineInitialized = false;

export function initCurrencyEngine() {
    if (currencyEngineInitialized) {
        updateAllCurrencyUI();
        return;
    }
    currencyEngineInitialized = true;

    // Attach click listeners with unified delegation
    document.addEventListener('click', (e) => {
        // 1. Toggle desktop dropdown
        const trigger = e.target.closest('#currency-dropdown-toggle');
        if (trigger) {
            e.preventDefault();
            e.stopPropagation();
            const menu = document.getElementById('currency-dropdown-menu');
            const langMenu = document.getElementById('language-dropdown-menu');
            if (langMenu) langMenu.classList.add('hidden');
            if (menu) menu.classList.toggle('hidden');
            return;
        }

        // 2. Toggle mobile dropdown
        const mobileTrigger = e.target.closest('#mobile-currency-dropdown-toggle');
        if (mobileTrigger) {
            e.preventDefault();
            e.stopPropagation();
            const mobileMenu = document.getElementById('mobile-currency-dropdown-menu');
            if (mobileMenu) mobileMenu.classList.toggle('hidden');
            return;
        }

        // 3. Currency option button selected
        const btn = e.target.closest('[data-set-currency]');
        if (btn) {
            e.preventDefault();
            const code = btn.getAttribute('data-set-currency');
            setCurrency(code);

            // Close currency dropdowns
            const dropdown = document.getElementById('currency-dropdown-menu');
            if (dropdown) dropdown.classList.add('hidden');
            const mobileDropdown = document.getElementById('mobile-currency-dropdown-menu');
            if (mobileDropdown) mobileDropdown.classList.add('hidden');
            return;
        }

        // 4. Click outside to close desktop dropdown
        const menu = document.getElementById('currency-dropdown-menu');
        if (menu && !menu.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });

    // Apply currency on start
    updateAllCurrencyUI();
}

// Expose on global window object for convenience
if (typeof window !== 'undefined') {
    window.GTM_Currency = {
        CURRENCIES,
        getActiveCurrency,
        setCurrency,
        formatPrice,
        convertAmount,
        updateAllCurrencyUI,
    };
}
