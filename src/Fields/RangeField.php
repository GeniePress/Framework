<?php

namespace GeniePress\Fields;

class RangeField extends NumberField
{

    /**
     * Set defaults
     */
    protected function setDefaults()
    {
        parent::setDefaults();
        $this->type('range');
        $this->metaQuery('NUMERIC');
    }
}

