<?php

namespace GeniePress\Fields;

class EmailField extends TextField
{

    /**
     * Set Defaults
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();
        $this->type('email');
    }

}
