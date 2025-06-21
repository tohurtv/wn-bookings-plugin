<?php namespace Tohur\Bookings\FormWidgets;

use Backend\Classes\FormWidgetBase;

class TimePicker extends FormWidgetBase
{
    protected $defaultAlias = 'tohur_bookings_timepicker';

    public function render()
    {
        // Render the partial with passed variables
        $this->prepareVars();
        return $this->makePartial('$/tohur/bookings/formwidgets/timepicker/partials/_timepicker.htm');
    }

    public function prepareVars()
    {
        $this->vars['field'] = $this->formField;
        $this->vars['value'] = $this->getLoadValue();
        $this->vars['fieldId'] = $this->getId();
        $this->vars['fieldName'] = $this->formField->getName();
        $this->vars['attributes'] = $this->formField->attributes ?: [];
    }

    // Save value (optional override)
    public function getSaveValue($value)
    {
        return $value;
    }
}
