<?php

namespace GeniePress\Fields;

class TimeField extends DateField
{

    /**
     * Set Defaults
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();
        $this->type('time_picker');
        $this->metaQuery('TIME');
        $this->displayFormat('g:i a');
        $this->returnFormat('H:i:s');
    }

}
