/**
 * Multilingual Translation Engine for GTM Real Estate
 * Powered by Google Translate (TranslateElement) instead of manual JS text replacements.
 */

export const LANGUAGES = {
    en: {
        code: 'en',
        name: 'English',
        nativeName: 'English',
        flag: '🇬🇧',
        short: 'EN',
    },
    am: {
        code: 'am',
        name: 'Amharic',
        nativeName: 'አማርኛ',
        flag: '🇪🇹',
        short: 'አማ',
    },
    om: {
        code: 'om',
        name: 'Afan Oromo',
        nativeName: 'Afaan Oromoo',
        flag: '🇪🇹',
        short: 'OM',
    },
    ti: {
        code: 'ti',
        name: 'Tigrinya',
        nativeName: 'ትግርኛ',
        flag: '🇪🇹',
        short: 'TI',
    },
    so: {
        code: 'so',
        name: 'Somali',
        nativeName: 'Soomaali',
        flag: '🇸🇴',
        short: 'SO',
    },
    ar: {
        code: 'ar',
        name: 'Arabic',
        nativeName: 'العربية',
        flag: '🇸🇦',
        short: 'AR',
    },
    fr: {
        code: 'fr',
        name: 'French',
        nativeName: 'Français',
        flag: '🇫🇷',
        short: 'FR',
    },
};

const STORAGE_KEY = 'gtm_preferred_language';

// Backward compatibility stubs
export const PHRASE_TRANSLATIONS = [];
export const WORD_TRANSLATIONS = {};

export function getActiveLanguage() {
    try {
        // 1. Check Google Translate cookie 'googtrans'
        const match = document.cookie.match(/(?:^|;\s*)googtrans=\/([^/]+)\/([^;]+)/);
        if (match && match[2]) {
            return match[2];
        }
        // 2. Check localStorage
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored) {
            return stored;
        }
    } catch (_) {}

    return document.documentElement.lang || 'en';
}

/**
 * Triggers Google Translate for the specified language code.
 */
export function triggerGoogleTranslate(targetLang) {
    if (!targetLang) return;

    const cookieVal = targetLang === 'en' ? '/en/en' : `/en/${targetLang}`;

    // Set cookie on root path and domain variants
    document.cookie = `googtrans=${cookieVal}; path=/;`;
    if (typeof window !== 'undefined' && window.location) {
        const host = window.location.hostname;
        if (host && host !== 'localhost' && host !== '127.0.0.1') {
            document.cookie = `googtrans=${cookieVal}; path=/; domain=${host};`;
            const parts = host.split('.');
            if (parts.length > 1) {
                const domain = '.' + parts.slice(-2).join('.');
                document.cookie = `googtrans=${cookieVal}; path=/; domain=${domain};`;
            }
        }
    }

    // Direct selection on Google Translate combo without polling loops
    const combo = document.querySelector('.goog-te-combo');
    if (combo) {
        if (combo.value !== targetLang) {
            combo.value = targetLang;
            combo.dispatchEvent(new Event('change'));
        }
    } else {
        // Fast fallback: reload page so Google Translate boots cleanly with the new cookie
        window.location.reload();
    }
}

/**
 * Update Header and UI buttons to reflect the selected language
 */
export function updateLanguageUI(lang) {
    const config = LANGUAGES[lang] || {
        code: lang,
        name: lang.toUpperCase(),
        nativeName: lang.toUpperCase(),
        flag: '🌐',
        short: lang.toUpperCase(),
    };

    document.querySelectorAll('[data-language-active-flag]').forEach((el) => {
        el.textContent = config.flag;
    });

    document.querySelectorAll('[data-language-active-code]').forEach((el) => {
        el.textContent = config.short;
    });

    document.querySelectorAll('[data-language-active-name]').forEach((el) => {
        el.textContent = config.nativeName;
    });

    document.querySelectorAll('[data-set-language]').forEach((btn) => {
        const btnLang = btn.getAttribute('data-set-language');
        if (btnLang === lang) {
            btn.classList.add('bg-[#1d3c34]', 'text-white');
            btn.classList.remove('text-[#1d3c34]', 'hover:bg-[#f3eadc]', 'hover:bg-[#f0e4cf]');
        } else {
            btn.classList.remove('bg-[#1d3c34]', 'text-white');
            btn.classList.add('text-[#1d3c34]');
        }
    });
}

/**
 * Switch language using Google Translate
 */
export function setLanguage(lang) {
    if (!lang) return;

    try {
        localStorage.setItem(STORAGE_KEY, lang);
    } catch (_) {}

    updateLanguageUI(lang);
    triggerGoogleTranslate(lang);

    // Sync with backend Laravel session asynchronously
    fetch(`/locale/${lang}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    }).catch(() => {});

    window.dispatchEvent(new CustomEvent('language-changed', {
        detail: {
            language: lang,
            config: LANGUAGES[lang] || null,
        },
    }));
}

/**
 * Dynamically load Google Translate script if not already on the page
 */
export function loadGoogleTranslateScript() {
    if (typeof window === 'undefined') return;

    // Ensure hidden container exists in DOM if not present
    if (!document.getElementById('google_translate_element')) {
        const el = document.createElement('div');
        el.id = 'google_translate_element';
        el.className = 'hidden';
        el.style.display = 'none';
        (document.body || document.documentElement).appendChild(el);
    }

    if (!window.googleTranslateElementInit) {
        window.googleTranslateElementInit = function () {
            if (window.google && window.google.translate) {
                let el = document.getElementById('google_translate_element');
                if (!el) {
                    el = document.createElement('div');
                    el.id = 'google_translate_element';
                    el.className = 'hidden';
                    el.style.display = 'none';
                    (document.body || document.documentElement).appendChild(el);
                }
                new window.google.translate.TranslateElement({
                    pageLanguage: 'en',
                    includedLanguages: 'en,am,om',
                    autoDisplay: false,
                }, 'google_translate_element');
            }
        };
    }

    if (document.getElementById('google-translate-script') || window.google?.translate?.TranslateElement) {
        return;
    }

    const script = document.createElement('script');
    script.id = 'google-translate-script';
    script.type = 'text/javascript';
    script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
    script.async = true;
    document.head.appendChild(script);
}

// Backward compatibility stub for translateRawString and translateEntireDocument
export function translateRawString(str) {
    return str;
}

export function translateEntireDocument(lang) {
    if (lang) {
        setLanguage(lang);
    }
}

let languageEngineInitialized = false;

export function initLanguageEngine() {
    if (languageEngineInitialized) {
        const activeLang = getActiveLanguage();
        updateLanguageUI(activeLang);
        return;
    }
    languageEngineInitialized = true;

    // Sync googtrans cookie from localStorage if present so translation applies automatically across all dashboards
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored && ['en', 'am', 'om'].includes(stored)) {
            const match = document.cookie.match(/(?:^|;\s*)googtrans=\/([^/]+)\/([^;]+)/);
            if (!match || match[2] !== stored) {
                const cookieVal = stored === 'en' ? '/en/en' : `/en/${stored}`;
                document.cookie = `googtrans=${cookieVal}; path=/;`;
            }
        }
    } catch (_) {}

    // Ensure Google Translate script is loaded
    loadGoogleTranslateScript();

    // Attach click listeners with unified delegation
    document.addEventListener('click', (e) => {
        // 1. Toggle desktop / portal language dropdown
        const trigger = e.target.closest('#language-dropdown-toggle, .language-dropdown-toggle');
        if (trigger) {
            e.preventDefault();
            e.stopPropagation();
            const wrapper = trigger.closest('[data-language-switcher-wrapper]') || trigger.parentElement;
            const menu = wrapper ? wrapper.querySelector('.language-dropdown-menu, #language-dropdown-menu') : document.getElementById('language-dropdown-menu');
            const curMenu = document.getElementById('currency-dropdown-menu');
            if (curMenu) curMenu.classList.add('hidden');
            if (menu) menu.classList.toggle('hidden');
            return;
        }

        // 2. Toggle mobile language dropdown
        const mobileTrigger = e.target.closest('#mobile-language-dropdown-toggle');
        if (mobileTrigger) {
            e.preventDefault();
            e.stopPropagation();
            const mobileMenu = document.getElementById('mobile-language-dropdown-menu');
            if (mobileMenu) mobileMenu.classList.toggle('hidden');
            return;
        }

        // 3. Language option button selected
        const btn = e.target.closest('[data-set-language]');
        if (btn) {
            e.preventDefault();
            const lang = btn.getAttribute('data-set-language');
            setLanguage(lang);

            document.querySelectorAll('#language-dropdown-menu, .language-dropdown-menu, #mobile-language-dropdown-menu').forEach(m => {
                m.classList.add('hidden');
            });
            return;
        }

        // 4. Click outside to close any open language dropdowns
        document.querySelectorAll('#language-dropdown-menu, .language-dropdown-menu').forEach(menu => {
            if (!menu.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });
    });

    // Sync initial UI state (Google Translate script handles translation via cookie automatically)
    const activeLang = getActiveLanguage();
    updateLanguageUI(activeLang);
}

// Global exposure for debugging and external scripts
if (typeof window !== 'undefined') {
    window.GTM_Language = {
        LANGUAGES,
        PHRASE_TRANSLATIONS,
        WORD_TRANSLATIONS,
        getActiveLanguage,
        setLanguage,
        triggerGoogleTranslate,
        loadGoogleTranslateScript,
        translateRawString,
        translateEntireDocument,
    };
}
