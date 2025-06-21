<?php
$fieldId = $this->getId();
$fieldValue = $field->value;
$inputName = $field->getName();
$attributes = $field->attributes ?? [];
?>
<style>
/* Style native time input to look like .form-control and respect dark mode */

.custom-timepicker {
    display: block;
    width: 100%;
    height: calc(2.25rem + 2px);
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    font-weight: 400;
    line-height: 1.5;
    color: var(--bs-body-color, #212529);
    background-color: var(--bs-body-bg, #fff);
    background-clip: padding-box;
    border: 1px solid var(--bs-border-color, #ced4da);
    border-radius: 0.375rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

/* Handle dark mode */
.theme-dark .custom-timepicker {
    background-color: #2b2b2b;
    color: #f1f1f1;
    border-color: #444;
}

/* Optional focus styling */
.custom-timepicker:focus {
    color: inherit;
    background-color: inherit;
    border-color: #86b7fe;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
/* Scoped only inside dark mode */
.dark-mode input.custom-timepicker {
  background-color: #1e1e1e !important;
  color: #f1f1f1 !important;
  border: 1px solid #444 !important;
  border-radius: 4px !important;
  padding: 6px 10px !important;
  font-size: 0.9rem !important;
  font-family: inherit !important;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.dark-mode input.custom-timepicker:focus {
  background-color: #1e1e1e !important;
  color: #f1f1f1 !important;
  border-color: #777 !important;
  outline: none !important;
  box-shadow: 0 0 0 0.2rem rgba(100, 100, 100, 0.25) !important;
}

/* Prevent autofill flash */
.dark-mode input.custom-timepicker:-webkit-autofill {
  box-shadow: 0 0 0px 1000px #1e1e1e inset !important;
  -webkit-text-fill-color: #f1f1f1 !important;
}

/* Native time picker icon */
.dark-mode input.custom-timepicker::-webkit-calendar-picker-indicator {
  filter: invert(80%) !important;
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
