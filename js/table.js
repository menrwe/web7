// Динамічна таблиця на jQuery: отримання даних, оновлення кнопкою, сортування за name та affiliation
$(function () {
    var API_URL   = 'http://lab.vntu.org/api-server/lab7.php';
    var PROXY_URL = 'proxy.php';          // резервний шлях через наш сервер
    var records   = [];                    // поточні дані таблиці
    var sortKey   = null;                  // за яким полем відсортовано

    /** Знаходить масив записів у будь-якій типовій структурі JSON */
    function extractRecords(data) {
        if (Array.isArray(data)) {
            if (data.length && typeof data[0] === 'object' && data[0] !== null && !Array.isArray(data[0])) {
                return data;
            }
            for (var i = 0; i < data.length; i++) {            // масив масивів – об’єднуємо
                var inner = extractRecords(data[i]);
                if (inner.length) { return inner; }
            }
            return [];
        }
        if (data && typeof data === 'object') {
            if ('name' in data) { return [data]; }
            var all = [];
            Object.keys(data).forEach(function (k) {
                var part = extractRecords(data[k]);
                if (part.length) { all = all.concat(part); }
            });
            return all;
        }
        return [];
    }

    function cell(v) {
        if (v === null || v === undefined) { return ''; }
        return typeof v === 'object' ? JSON.stringify(v) : String(v);
    }

    function render() {
        var $head = $('#dataTable thead').empty();
        var $body = $('#dataTable tbody').empty();
        if (!records.length) { $body.append('<tr><td>Даних немає</td></tr>'); return; }

        // колонки: спочатку name та affiliation, потім усі інші поля
        var cols = [];
        records.forEach(function (r) { Object.keys(r).forEach(function (k) { if (cols.indexOf(k) < 0) { cols.push(k); } }); });
        ['affiliation', 'name'].forEach(function (k) {
            var i = cols.indexOf(k);
            if (i > -1) { cols.splice(i, 1); cols.unshift(k); }
        });

        var $tr = $('<tr>');
        cols.forEach(function (c) { $tr.append($('<th>').text(c + (c === sortKey ? ' ▲' : ''))); });
        $head.append($tr);

        records.forEach(function (r) {
            var $row = $('<tr>');
            cols.forEach(function (c) { $row.append($('<td>').text(cell(r[c]))); });   // .text() – захист від XSS
            $body.append($row);
        });
    }

    function sortBy(key) {
        records.sort(function (a, b) {
            return cell(a[key]).localeCompare(cell(b[key]), 'uk', { sensitivity: 'base' });
        });
        sortKey = key;
        render();
        $('#status').text('Відсортовано за полем «' + key + '». Записів: ' + records.length);
    }

    function onData(text, via) {
        var json;
        try { json = JSON.parse(text); } catch (e) { $('#status').text('Сервер повернув не JSON.'); return; }
        records = extractRecords(json);
        sortKey = null;
        render();
        $('#status').text('Оновлено о ' + new Date().toLocaleTimeString('uk-UA') + ' (' + via + '). Записів: ' + records.length);
    }

    function load() {
        $('#status').text('Завантаження…');
        // 1) пробуємо звернутися до API напряму
        $.ajax({ url: API_URL, method: 'GET', dataType: 'text', timeout: 8000 })
            .done(function (t) { onData(t, 'напряму з API'); })
            .fail(function () {
                // 2) якщо браузер заблокував запит (CORS / http у https-сторінці) – через proxy.php
                $.ajax({ url: PROXY_URL, method: 'GET', dataType: 'text', timeout: 12000 })
                    .done(function (t) { onData(t, 'через proxy.php'); })
                    .fail(function () { $('#status').text('Не вдалося отримати дані з API.'); });
            });
    }

    $('#refresh').on('click', load);
    $('#sortName').on('click', function () { sortBy('name'); });
    $('#sortAff').on('click', function () { sortBy('affiliation'); });
    load();
});
