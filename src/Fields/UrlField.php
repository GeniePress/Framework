<?php

namespace GeniePress\Fields;

class UrlField extends TextField
{

    /**
     * Set Defaults
     */
    protected function setDefaults()
    {
        parent::setDefaults();
        $this->type('url');
    }

}
