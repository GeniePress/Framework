<?php

namespace GeniePress\Fields;

class DateTimeField extends DateField
{

    /**
     * Set defaults
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();

        $this->type('date_time_picker');
        $this->metaQuery('DATETIME');
        $this->displayFormat('d/m/Y g:i a');
        $this->returnFormat('Y-m-d H:i:s');
    }

}
