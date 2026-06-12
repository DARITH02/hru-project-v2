@if (app()->getLocale() === 'km')
    <style>
        html:lang(km) body,
        html:lang(km) button,
        html:lang(km) input,
        html:lang(km) textarea,
        html:lang(km) select {
            font-family: "Noto Sans Khmer", "Khmer OS Siemreap", "Khmer OS Battambang", "Leelawadee UI", ui-sans-serif, system-ui, sans-serif !important;
        }

        html:lang(km) button,
        html:lang(km) label,
        html:lang(km) th,
        html:lang(km) .label,
        html:lang(km) .btn,
        html:lang(km) .badge,
        html:lang(km) .hint,
        html:lang(km) .help {
            letter-spacing: 0 !important;
            line-height: 1.5;
        }
    </style>
@endif

@php
    $standaloneNextLocale = app()->getLocale() === 'km' ? 'en' : 'km';
    $standaloneNextLocaleLabel = config("app.supported_locales.$standaloneNextLocale");
@endphp

<style>
    .standalone-language-switcher {
        position: fixed;
        top: 14px;
        right: 14px;
        z-index: 9999;
        display: inline-flex;
        padding: 3px;
        border: 1px solid rgba(148, 163, 184, .35);
        border-radius: 10px;
        background: rgba(255, 255, 255, .9);
        box-shadow: 0 10px 28px rgba(15, 23, 42, .12);
        backdrop-filter: blur(10px);
    }

    [data-theme="dark"] .standalone-language-switcher,
    html[data-theme="dark"] .standalone-language-switcher {
        background: rgba(15, 23, 42, .78);
        border-color: rgba(148, 163, 184, .24);
    }

    .standalone-language-switcher button {
        min-width: 42px;
        height: 30px;
        border: 0;
        border-radius: 7px;
        background: transparent;
        color: #0f172a;
        font-size: 11px;
        font-weight: 800;
        line-height: 1;
        cursor: pointer;
    }

    [data-theme="dark"] .standalone-language-switcher button,
    html[data-theme="dark"] .standalone-language-switcher button {
        color: #f8fafc;
    }
</style>

<form class="standalone-language-switcher" action="{{ route('language.switch', $standaloneNextLocale) }}" method="POST"
    aria-label="{{ __('admin.language') }}">
    @csrf
    <button type="submit" title="{{ $standaloneNextLocaleLabel }}">
        {{ $standaloneNextLocale === 'km' ? 'ខ្មែរ' : 'EN' }}
    </button>
</form>

<script>
    (function () {
        const legacyTranslations = @json(is_array(trans('admin_legacy')) ? trans('admin_legacy') : []);
        const normalizeLegacyText = (value) => String(value ?? '').replace(/\s+/g, ' ').trim();

        window.__t = window.__t || function(value) {
            const text = normalizeLegacyText(value);
            if (legacyTranslations[text]) return legacyTranslations[text];
            return value;
        };

        function translateLegacyNode(root = document.body) {
            if (!legacyTranslations || !Object.keys(legacyTranslations).length || !root) return;

            const translateElementAttributes = (el) => {
                if (!el || el.nodeType !== Node.ELEMENT_NODE) return;
                ['placeholder', 'title', 'aria-label'].forEach((attr) => {
                    if (!el.hasAttribute(attr)) return;
                    const current = el.getAttribute(attr);
                    const translated = window.__t(current);
                    if (translated !== current) el.setAttribute(attr, translated);
                });
            };

            const translateTextNode = (node) => {
                const parent = node.parentElement;
                if (!parent || ['SCRIPT', 'STYLE', 'TEXTAREA', 'INPUT'].includes(parent.tagName)) return;
                const original = node.nodeValue;
                const trimmed = normalizeLegacyText(original);
                if (!trimmed) return;
                const translated = window.__t(trimmed);
                if (translated === trimmed) return;
                node.nodeValue = original.replace(trimmed, translated);
            };

            if (root.nodeType === Node.TEXT_NODE) {
                translateTextNode(root);
                return;
            }

            if (root.nodeType === Node.ELEMENT_NODE) {
                translateElementAttributes(root);
            }

            const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT | NodeFilter.SHOW_ELEMENT);
            let node;
            while ((node = walker.nextNode())) {
                if (node.nodeType === Node.TEXT_NODE) {
                    translateTextNode(node);
                } else {
                    translateElementAttributes(node);
                }
            }
        }

        if (Object.keys(legacyTranslations).length) {
            document.title = window.__t(document.title);

            const nativeConfirm = window.confirm.bind(window);
            window.confirm = (message) => nativeConfirm(window.__t(message));

            const bootLegacyTranslator = () => {
                translateLegacyNode();

                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        mutation.addedNodes.forEach((node) => translateLegacyNode(node));
                        if (mutation.type === 'characterData') translateLegacyNode(mutation.target.parentElement);
                    });
                });
                observer.observe(document.body, {
                    childList: true,
                    characterData: true,
                    subtree: true,
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootLegacyTranslator, { once: true });
            } else {
                bootLegacyTranslator();
            }
        }
    })();
</script>
