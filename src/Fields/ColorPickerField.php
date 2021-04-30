<?php

namespace GeniePress\Fields;

use GeniePress\Abstracts\Field;

class ColorPickerField extends Field
{


    protected function setDefaults()
    {
        parent::setDefaults();
        $this->type('color_picker');
    }
}
