// Завантаження текстового файлу з жартами через XMLHttpRequest та вивід випадкового жарту
var jokeBox = document.getElementById('joke');
var jokes = null;                       // кеш: файл завантажуємо один раз

function showRandom() {
    var i = Math.floor(Math.random() * jokes.length);
    jokeBox.textContent = jokes[i];
}

function loadJokes() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4) {
            if (this.status == 200) {
                // кожен рядок файлу – окремий жарт; порожні рядки відкидаємо
                jokes = this.responseText.split(/\r?\n/).filter(function (l) { return l.trim() !== ''; });
                showRandom();
            } else {
                jokeBox.textContent = 'Не вдалося завантажити файл із жартами (код ' + this.status + ').';
            }
        }
    };
    xhttp.open('GET', 'jokes.txt', true);
    xhttp.send();
}

document.getElementById('btn').addEventListener('click', function () {
    if (jokes) { showRandom(); } else { loadJokes(); }
});
loadJokes();                            // перший жарт – одразу після відкриття сторінки
