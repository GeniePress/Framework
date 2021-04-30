<?php

namespace GeniePress\Fields;

class EmailField extends TextField
{


    protected function setDefaults()
    {
        parent::setDefaults();
        $this->type('email');
    }

}
