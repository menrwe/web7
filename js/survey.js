// Надсилання анкети за допомогою XMLHttpRequest (без перезавантаження сторінки)
(function () {
    var form      = document.getElementById('surveyForm');
    var errorsBox = document.getElementById('errors');
    var resultBox = document.getElementById('result');
    var formCard  = document.getElementById('formCard');
    var sendBtn   = document.getElementById('sendBtn');

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function showErrors(list) {
        errorsBox.innerHTML = '<ul>' + list.map(function (m) { return '<li>' + esc(m) + '</li>'; }).join('') + '</ul>';
        errorsBox.hidden = false;
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();                 // скасовуємо стандартну відправку форми
        errorsBox.hidden = true;
        sendBtn.disabled = true;

        var xhr = new XMLHttpRequest();         // 1. створюємо об’єкт запиту
        xhr.open('POST', 'submit.php', true);   // 2. тип запиту, URL, асинхронний режим
        xhr.onreadystatechange = function () {  // 3. обробник зміни стану
            if (xhr.readyState !== 4) { return; }
            sendBtn.disabled = false;

            var resp;
            try { resp = JSON.parse(xhr.responseText); }
            catch (e) { showErrors(['Помилка сервера: некоректна відповідь.']); return; }

            if (xhr.status === 200 && resp.success) {
                formCard.hidden = true;
                resultBox.innerHTML =
                    '<h2>Дякуємо, ' + esc(resp.name) + '!</h2>' +
                    '<p>Вашу відповідь збережено без перезавантаження сторінки (AJAX).</p>' +
                    '<p><strong>Дата і час заповнення форми:</strong> ' + esc(resp.submitted_at) + '</p>' +
                    '<p>Файл відповіді: <code>' + esc(resp.file) + '</code></p>' +
                    '<p><button type="button" id="again">Заповнити ще раз</button></p>';
                resultBox.hidden = false;
                document.getElementById('again').addEventListener('click', function () {
                    form.reset();
                    resultBox.hidden = true;
                    formCard.hidden = false;
                });
            } else {
                showErrors(resp.errors || ['Невідома помилка.']);
            }
        };
        xhr.onerror = function () { sendBtn.disabled = false; showErrors(['Не вдалося зв’язатися із сервером.']); };
        xhr.send(new FormData(form));           // 4. надсилаємо дані форми
    });
})();
