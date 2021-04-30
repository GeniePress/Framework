<?php

namespace GeniePress\Fields;

class UrlField extends TextField
{


    protected function setDefaults()
    {
        parent::setDefaults();
        $this->type('url');
    }

}
