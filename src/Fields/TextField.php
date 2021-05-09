<?php

namespace GeniePress\Fields;

use GeniePress\Abstracts\Field;

class TextField extends Field
{

    /**
     * Should this field be disabled?
     *
     * @param  bool  $disabled
     *
     * @return $this
     */
    public function disabled(bool $disabled): TextField
    {
        return $this->set('disabled', $disabled);
    }



    /**
     * The maximum length accepted
     *
     * @param  int  $maxLength
     *
     * @return $this
     */
    public function maxLength(int $maxLength): TextField
    {
        return $this->set('maxlength', $maxLength);
    }



    /**
     * Sets the placeholder for the field.
     *
     * @param $placeholder
     *
     * @return $this
     */
    public function placeholder($placeholder): TextField
    {
        return $this->set('placeholder', $placeholder);
    }



    /**
     * Set Defaults
     */
    protected function setDefaults()
    {
        parent::setDefaults();
        $this->type('text');
    }

}
