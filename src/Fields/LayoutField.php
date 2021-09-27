<?php

namespace GeniePress\Fields;

class LayoutField extends GroupField
{

    /**
     * Specify the maximum posts allowed to be selected. Defaults to 0
     *
     * @param  int  $number
     *
     * @return $this
     */
    public function max(int $number): LayoutField
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
    public function min(int $number): LayoutField
    {
        return $this->set('min', $number);
    }



    /**
     * Set Defaults
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();
        $this->type('layout');
        $this->layout('block');
    }

}
