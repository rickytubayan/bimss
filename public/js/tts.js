(function () {
    'use strict';

    const TTS = {
        supported: typeof window.speechSynthesis !== 'undefined',
        utterance: null,
        readerElement: null,
        language: 'en',

        init() {
            if (!this.supported) return;
            this.readerElement = document.getElementById('tts-reader');

            // Auto-read elements with data-read-aloud attribute
            document.querySelectorAll('[data-read-aloud]').forEach(el => {
                if (!el.hasAttribute('data-tts-bound')) {
                    el.setAttribute('data-tts-bound', '');
                    el.addEventListener('click', () => {
                        this.speak(el.getAttribute('data-read-aloud') || el.textContent);
                    });
                }
            });
        },

        speak(text, lang) {
            if (!this.supported || !text) return;
            this.stop();
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = lang || this.language || 'en-PH';
            utterance.rate = 0.95;
            utterance.pitch = 1;

            if (this.readerElement) {
                this.readerElement.textContent = text;
                this.readerElement.classList.add('active');
                utterance.onend = () => {
                    if (this.readerElement) this.readerElement.classList.remove('active');
                };
            }

            const voices = window.speechSynthesis.getVoices();
            const preferred = voices.find(v => v.lang.toLowerCase().includes('fil'))
                || voices.find(v => v.lang.toLowerCase().includes('en-ph'))
                || voices.find(v => v.lang.toLowerCase().startsWith('en'));
            if (preferred) utterance.voice = preferred;

            window.speechSynthesis.speak(utterance);
            this.utterance = utterance;
        },

        stop() {
            if (this.supported) {
                window.speechSynthesis.cancel();
            }
            if (this.readerElement) {
                this.readerElement.classList.remove('active');
            }
        },

        isSpeaking() {
            return this.supported && window.speechSynthesis.speaking;
        },
    };

    window.BIMSTTS = TTS;
})();
