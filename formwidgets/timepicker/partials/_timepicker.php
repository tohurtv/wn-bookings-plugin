<?php
$fieldId = $this->getId();
$fieldValue = $field->value;
$inputName = $field->getName();
$attributes = $field->attributes ?? [];
?>
<input
    type="time"
    id="<?= e($fieldId) ?>"
    name="<?= e($inputName) ?>"
    value="<?= e($fieldValue) ?>"
    class="custom-timepicker"
    <?php foreach ($attributes as $attr => $val): ?>
        <?= e($attr) ?>="<?= e($val) ?>"
    <?php endforeach; ?>
>
