<?php

namespace GeniePress\Fields;

class PasswordField extends TextField
{


    protected function setDefaults()
    {
        parent::setDefaults();
        $this->type('password');
    }


}
