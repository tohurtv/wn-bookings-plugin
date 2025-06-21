<?php
$fieldId = $this->getId();
$fieldValue = $field->value;
$inputName = $field->getName();
$attributes = $field->attributes ?? [];
?>
<style>
/* Base style */
input.custom-timepicker {
  background-color: #1e1e1e !important; /* Dark background */
  color: #f1f1f1 !important;            /* Light text */
  border: 1px solid #444 !important;
  border-radius: 4px;
  padding: 6px 10px;
  font-size: 0.9rem;
  font-family: inherit;
}

/* Ensure it stays dark on focus */
input.custom-timepicker:focus {
  background-color: #1e1e1e !important;
  color: #f1f1f1 !important;
  border-color: #777;
  outline: none;
  box-shadow: 0 0 0 0.2rem rgba(100, 100, 100, 0.25);
}

/* Prevent browser autofill flash (especially in Chrome) */
input.custom-timepicker:-webkit-autofill {
  box-shadow: 0 0 0px 1000px #1e1e1e inset !important;
  -webkit-text-fill-color: #f1f1f1 !important;
}

/* Optional: style the native time picker dropdown icon */
input.custom-timepicker::-webkit-calendar-picker-indicator {
  filter: invert(80%);
}

</style>
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
