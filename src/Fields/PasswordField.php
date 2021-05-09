<?php

namespace GeniePress\Fields;

class PasswordField extends TextField
{

    /**
     * Set Defaults
     */
    protected function setDefaults()
    {
        parent::setDefaults();
        $this->type('password');
    }

}
