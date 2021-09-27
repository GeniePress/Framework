<?php

namespace GeniePress\Fields;

use GeniePress\Abstracts\Field;

class ColorPickerField extends Field
{

    /**
     * Set Defaults
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();
        $this->type('color_picker');
    }
}
