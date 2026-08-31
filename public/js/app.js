(function () {
    'use strict';

    const App = {
        init() {
            this.initMobileNav();
            this.initAlertDismiss();
            this.initLanguageSelector();
            this.initConfirmationDialogs();
            this.initEmergencyButton();
        },

        initMobileNav() {
            const toggle = document.querySelector('.nav-toggle');
            const navList = document.querySelector('.nav-list');
            if (toggle && navList) {
                toggle.addEventListener('click', () => {
                    const open = navList.classList.toggle('open');
                    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                });
            }

            const sidebarToggle = document.querySelector('.sidebar-toggle');
            const sidebar = document.querySelector('.admin-sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('open');
                    if (overlay) overlay.classList.toggle('active');
                });
                if (overlay) {
                    overlay.addEventListener('click', () => {
                        sidebar.classList.remove('open');
                        overlay.classList.remove('active');
                    });
                }
            }
        },

        initAlertDismiss() {
            document.querySelectorAll('.alert').forEach(alert => {
                const close = document.createElement('button');
                close.innerHTML = '&times;';
                close.className = 'alert-close';
                close.setAttribute('aria-label', 'Dismiss');
                close.style.cssText = 'float:right;border:none;background:none;font-size:1.2rem;cursor:pointer;';
                close.addEventListener('click', () => alert.remove());
                alert.appendChild(close);

                setTimeout(() => {
                    if (alert.parentNode) alert.style.transition = 'opacity 0.5s';
                    if (alert.parentNode) alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }, 8000);
            });
        },

        initLanguageSelector() {
            const langSelect = document.getElementById('lang-select');
            if (langSelect) {
                langSelect.addEventListener('change', () => {
                    fetch('./api/language/set?lang=' + encodeURIComponent(langSelect.value), {
                        method: 'GET',
                        credentials: 'same-origin',
                    }).then(() => { window.location.reload(); });
                });
            }
        },

        initConfirmationDialogs() {
            document.querySelectorAll('[data-confirm]').forEach(el => {
                el.addEventListener('submit', (e) => {
                    if (!window.confirm(el.getAttribute('data-confirm') || 'Are you sure?')) {
                        e.preventDefault();
                    }
                });
                el.querySelectorAll('button[type="submit"]').forEach(btn => {
                    if (!el.hasAttribute('data-confirm')) {
                        el.setAttribute('data-confirm', btn.getAttribute('data-confirm') || 'Are you sure?');
                    }
                });
            });
        },

        initEmergencyButton() {
            const btn = document.getElementById('emergency-panic');
            if (btn) {
                btn.addEventListener('click', () => {
                    const msg = 'Are you sure you want to trigger the emergency alert? This will notify all users.';
                    if (window.confirm(msg)) {
                        fetch(btn.getAttribute('data-url') || './api/emergency/trigger', {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            credentials: 'same-origin',
                        }).then(r => r.json()).then(res => {
                            alert(res.message || 'Emergency alert triggered.');
                        });
                    }
                });
            }
        },
    };

    document.addEventListener('DOMContentLoaded', () => App.init());

    window.App = App;
})();
