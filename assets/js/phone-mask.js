/**
 * Маска и валидация телефона для инпутов (без CDN).
 * Формат: +7 (XXX) XXX-XX-XX. Поддержка полей type="tel" и CF7.
 */
(function () {
    var digitCount = 11; // 7 + 10 цифр

    function digitsOnly(str) {
        return (str || '').replace(/\D/g, '');
    }

    function formatPhone(digits) {
        if (!digits || digits.length === 0) return '';
        var d = digits.slice(0, 11);
        if (d[0] === '8') d = '7' + d.slice(1);
        if (d[0] !== '7') d = '7' + d;
        var s = d.length;
        if (s <= 1) return '+7' + (d.length ? ' ' + d.slice(1) : '');
        if (s <= 4) return '+7 (' + d.slice(1);
        if (s <= 7) return '+7 (' + d.slice(1, 4) + ') ' + d.slice(4);
        if (s <= 9) return '+7 (' + d.slice(1, 4) + ') ' + d.slice(4, 7) + '-' + d.slice(7);
        return '+7 (' + d.slice(1, 4) + ') ' + d.slice(4, 7) + '-' + d.slice(7, 9) + '-' + d.slice(9, 11);
    }

    function applyMask(input) {
        if (!input || input.dataset.phoneMask === 'applied') return;
        input.dataset.phoneMask = 'applied';

        input.addEventListener('input', function () {
            var pos = input.selectionStart;
            var oldLen = input.value.length;
            var digits = digitsOnly(input.value);
            if (digits.charAt(0) === '8') digits = '7' + digits.slice(1);
            if (digits.charAt(0) !== '7' && digits.length > 0) digits = '7' + digits;
            digits = digits.slice(0, digitCount);
            input.value = formatPhone(digits);
            input.setCustomValidity(digits.length >= 11 ? '' : 'Введите номер из 11 цифр');
            var newLen = input.value.length;
            var newPos = Math.max(0, pos + (newLen - oldLen));
            if (newPos > input.value.length) newPos = input.value.length;
            input.setSelectionRange(newPos, newPos);
        });

        input.addEventListener('focus', function () {
            if (digitsOnly(input.value).length === 0) input.value = '+7 ';
        });

        input.addEventListener('blur', function () {
            var digits = digitsOnly(input.value);
            if (digits.length < 11 && digits.length > 0) {
                input.setCustomValidity('Введите номер из 11 цифр');
            } else {
                input.setCustomValidity('');
            }
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && digitsOnly(input.value).length <= 1) {
                input.value = '';
                input.setCustomValidity('');
            }
        });
    }

    function init() {
        var inputs = document.querySelectorAll('input[type="tel"], input[name*="tel"], input[name*="phone"], .wpcf7-tel');
        inputs.forEach(applyMask);

        // CF7 может подгружать формы динамически
        if (typeof MutationObserver !== 'undefined') {
            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (m) {
                    if (m.addedNodes.length) {
                        [].slice.call(m.addedNodes).forEach(function (node) {
                            if (node.nodeType === 1) {
                                var tel = node.querySelector && node.querySelectorAll('input[type="tel"], input[name*="tel"], input[name*="phone"], .wpcf7-tel');
                                if (tel && tel.length) [].slice.call(tel).forEach(applyMask);
                                if (node.matches && node.matches('input[type="tel"], input[name*="tel"], input[name*="phone"], .wpcf7-tel')) applyMask(node);
                            }
                        });
                    }
                });
            });
            observer.observe(document.body, { childList: true, subtree: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
