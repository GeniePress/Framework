<?php

namespace GeniePress\Fields;

class RangeField extends NumberField
{

    /**
     * Set defaults
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();
        $this->type('range');
        $this->metaQuery('NUMERIC');
    }
}

