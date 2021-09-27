<?php

namespace GeniePress\Fields;

class UrlField extends TextField
{

    /**
     * Set Defaults
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();
        $this->type('url');
    }

}
