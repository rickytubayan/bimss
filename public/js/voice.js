(function () {
    'use strict';

    const Voice = {
        supported: typeof window.SpeechRecognition !== 'undefined'
            || typeof window.webkitSpeechRecognition !== 'undefined',

        attachTo(input) {
            if (!this.supported) return;

            const Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            const recognition = new Recognition();
            recognition.lang = 'en-PH';
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;

            const wrapper = document.createElement('span');
            wrapper.className = 'voice-input-wrap';
            wrapper.style.cssText = 'position:relative;display:inline-block;';
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);

            const micBtn = document.createElement('button');
            micBtn.type = 'button';
            micBtn.className = 'voice-input-btn';
            micBtn.setAttribute('aria-label', 'Use voice input');
            micBtn.textContent = '🎤';
            micBtn.style.cssText = 'position:absolute;right:8px;top:50%;transform:translateY(-50%);border:none;background:transparent;font-size:1.2rem;cursor:pointer;padding:4px;';
            wrapper.appendChild(micBtn);

            micBtn.addEventListener('click', () => {
                try {
                    recognition.start();
                    micBtn.style.color = '#d93025';
                } catch (e) {}
            });

            recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                input.value = transcript;
                input.dispatchEvent(new Event('input', { bubbles: true }));
                micBtn.style.color = '';
            };

            recognition.onerror = () => {
                micBtn.style.color = '';
            };

            recognition.onend = () => {
                micBtn.style.color = '';
            };
        },
    };

    window.BIMSVoice = Voice;
})();
