<?php

namespace GeniePress\Fields;

class PasswordField extends TextField
{

    /**
     * Set Defaults
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();
        $this->type('password');
    }

}
