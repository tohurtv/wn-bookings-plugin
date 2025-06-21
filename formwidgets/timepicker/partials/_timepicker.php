<?php
$parentIndex = $this->parentIndex ?? 0;
$childIndex = $this->index ?? 0;
$keyName = $field->fieldName; // NOT getName()
$fieldId = $this->getId();
$fieldValue = $field->value;
$attributes = $field->attributes ?? [];

$inputName = "working_schedule[{$parentIndex}][time_blocks][{$childIndex}][{$keyName}]";
$inputId = "{$fieldId}_{$parentIndex}_{$childIndex}";
?>
<input
    type="time"
    id="<?= e($inputId) ?>"
    name="<?= e($inputName) ?>"
    value="<?= e($fieldValue) ?>"
    class="custom-timepicker"
    <?php foreach ($attributes as $attr => $val): ?>
        <?= e($attr) ?>="<?= e($val) ?>"
    <?php endforeach; ?>
>

