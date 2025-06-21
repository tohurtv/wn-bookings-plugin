<?php
$fieldId = $this->getId();
$fieldValue = $field->value;
$inputName = $field->getName();
$attributes = $field->attributes ?? [];
?>
<style>
input.custom-timepicker {
  background-color: #fff !important;
  color: #212529 !important;
  width: 100%;
  border: 1px solid #ced4da !important;
  border-radius: 4px !important;
  padding: 6px 10px !important;
  font-size: 0.9rem !important;
  font-family: inherit !important;
}

input.custom-timepicker:focus {
  background-color: #fff !important;
  color: #212529 !important;
  border-color: #86b7fe !important;
  outline: none !important;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
}

input.custom-timepicker::-webkit-calendar-picker-indicator {
  filter: none !important;
  cursor: pointer;
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
