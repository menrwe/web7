<?php
// Сторінка анкети. Надсилання виконується AJAX-ом (js/survey.js -> submit.php)
require_once __DIR__ . '/includes/functions.php';

$title = 'Анкета (AJAX): ШІ-помічники у навчанні студентів';
require __DIR__ . '/includes/header.php';
?>

<div class="card" id="formCard">
    <h2>Анкета: ШІ-помічники у навчанні студентів</h2>
    <div id="errors" class="errors" hidden></div>

    <form id="surveyForm" method="post" action="submit.php">
        <label class="q" for="name">Ваше ім’я</label>
        <input type="text" id="name" name="name" required maxlength="100">

        <label class="q" for="email">Ваш email</label>
        <input type="email" id="email" name="email" required>

        <?php foreach (survey_questions() as $key => $q): ?>
            <label class="q" <?= $q['type'] === 'select' || $q['type'] === 'text' ? 'for="' . e($key) . '"' : '' ?>><?= e($q['text']) ?></label>

            <?php if ($q['type'] === 'radio'): ?>
                <?php foreach ($q['options'] as $opt): ?>
                    <label class="opt"><input type="radio" name="<?= e($key) ?>" value="<?= e($opt) ?>" required> <?= e($opt) ?></label>
                <?php endforeach; ?>

            <?php elseif ($q['type'] === 'checkbox'): ?>
                <?php foreach ($q['options'] as $opt): ?>
                    <label class="opt"><input type="checkbox" name="<?= e($key) ?>[]" value="<?= e($opt) ?>"> <?= e($opt) ?></label>
                <?php endforeach; ?>

            <?php elseif ($q['type'] === 'select'): ?>
                <select id="<?= e($key) ?>" name="<?= e($key) ?>" required>
                    <option value="">— оберіть —</option>
                    <?php foreach ($q['options'] as $opt): ?>
                        <option value="<?= e($opt) ?>"><?= e($opt) ?></option>
                    <?php endforeach; ?>
                </select>

            <?php else: ?>
                <textarea id="<?= e($key) ?>" name="<?= e($key) ?>" maxlength="1000" required></textarea>
            <?php endif; ?>
        <?php endforeach; ?>

        <p style="margin-top:22px"><button type="submit" id="sendBtn">Надіслати відповідь</button></p>
    </form>
</div>

<div class="card success" id="result" hidden></div>

<script src="js/survey.js"></script>
<?php require __DIR__ . '/includes/footer.php'; ?>
