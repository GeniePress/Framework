<?php

namespace GeniePress\Fields;

use GeniePress\Abstracts\Field;

class FlexibleContentField extends Field
{

    /**
     * Sets a label for the add Button
     *
     * @param $label
     *
     * @return $this
     */
    public function buttonLabel($label): FlexibleContentField
    {
        return $this->set('button_label', $label);
    }



    /**
     * Specify the maximum posts allowed to be selected. Defaults to 0
     *
     * @param  int  $number
     *
     * @return $this
     */
    public function max(int $number): FlexibleContentField
    {
        return $this->set('max', $number);
    }



    /**
     * Specify the minimum posts required to be selected. Defaults to 0
     *
     * @param  int  $number
     *
     * @return $this
     */
    public function min(int $number): FlexibleContentField
    {
        return $this->set('min', $number);
    }



    /**
     * Add Fields
     *
     * @param  array  $fields
     *
     * @return $this
     */
    public function withLayouts(array $fields): FlexibleContentField
    {
        return $this->set('layouts', $fields);
    }



    /**
     * Set Defaults
     */
    protected function setDefaults()
    {
        parent::setDefaults();
        $this->type('flexible_content');
    }

}
