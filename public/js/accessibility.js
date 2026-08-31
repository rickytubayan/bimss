(function () {
    'use strict';

    const STORAGE_KEY = 'bims_accessibility';

    const defaults = {
        fontScale: 1,
        darkMode: false,
        highContrast: false,
        readAloud: false,
    };

    let settings = loadSettings();

    function loadSettings() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            return saved ? Object.assign({}, defaults, JSON.parse(saved)) : Object.assign({}, defaults);
        } catch (e) {
            return Object.assign({}, defaults);
        }
    }

    function saveSettings() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(settings));
        } catch (e) {}
    }

    function applySettings() {
        const html = document.documentElement;
        html.setAttribute('data-theme', settings.darkMode ? 'dark' : 'light');
        html.setAttribute('data-high-contrast', settings.highContrast ? 'true' : 'false');

        const scalePercent = Math.round(settings.fontScale * 100);
        html.style.setProperty('--font-scale', settings.fontScale);

        const indicator = document.getElementById('font-scale-indicator');
        if (indicator) indicator.textContent = scalePercent + '%';

        const darkToggle = document.getElementById('dark-mode-toggle');
        const contrastToggle = document.getElementById('high-contrast-toggle');
        if (darkToggle) darkToggle.setAttribute('aria-pressed', settings.darkMode ? 'true' : 'false');
        if (contrastToggle) contrastToggle.setAttribute('aria-pressed', settings.highContrast ? 'true' : 'false');
    }

    function init() {
        applySettings();

        document.querySelectorAll('[data-font-scale]').forEach(btn => {
            btn.addEventListener('click', () => {
                const action = btn.getAttribute('data-font-scale');
                if (action === 'increase') {
                    settings.fontScale = Math.min(2, Math.round((settings.fontScale + 0.25) * 100) / 100);
                } else {
                    settings.fontScale = Math.max(0.75, Math.round((settings.fontScale - 0.25) * 100) / 100);
                }
                saveSettings();
                applySettings();
            });
        });

        const darkToggle = document.getElementById('dark-mode-toggle');
        if (darkToggle) {
            darkToggle.addEventListener('click', () => {
                settings.darkMode = !settings.darkMode;
                saveSettings();
                applySettings();
            });
        }

        const contrastToggle = document.getElementById('high-contrast-toggle');
        if (contrastToggle) {
            contrastToggle.addEventListener('click', () => {
                settings.highContrast = !settings.highContrast;
                saveSettings();
                applySettings();
            });
        }

        const readAloudToggle = document.getElementById('read-aloud-toggle');
        if (readAloudToggle) {
            readAloudToggle.addEventListener('click', () => {
                settings.readAloud = !settings.readAloud;
                saveSettings();
                readAloudToggle.setAttribute('aria-pressed', settings.readAloud ? 'true' : 'false');
                if (settings.readAloud && window.BIMSTTS) {
                    const heading = document.querySelector('h1, .page-title');
                    if (heading) window.BIMSTTS.speak(heading.textContent);
                }
            });
        }

        const resetBtn = document.getElementById('acc-reset');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                settings = Object.assign({}, defaults);
                saveSettings();
                applySettings();
                if (window.BIMSTTS) window.BIMSTTS.stop();
            });
        }

        // Voice input: add mic buttons to text inputs
        if (window.BIMSVoice) {
            document.querySelectorAll('input[type="text"], input:not([type]), textarea').forEach(input => {
                window.BIMSVoice.attachTo(input);
            });
        }

        // Read-aloud buttons
        document.querySelectorAll('[data-tts]').forEach(el => {
            if (!el.hasAttribute('data-tts-bound')) {
                el.setAttribute('data-tts-bound', '');
                el.addEventListener('click', () => {
                    if (window.BIMSTTS) {
                        const text = el.getAttribute('data-tts');
                        window.BIMSTTS.speak(text);
                    }
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => init());
})();
