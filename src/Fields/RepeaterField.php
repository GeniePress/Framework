<?php

namespace GeniePress\Fields;

class RepeaterField extends GroupField
{

    /**
     * Sets a label for the add Button
     *
     * @param $label
     *
     * @return $this
     */
    public function buttonLabel($label): RepeaterField
    {
        return $this->set('button_label', $label);
    }



    /**
     * @param $collapsed
     *
     * @return $this
     */
    public function collapsed($collapsed): RepeaterField
    {
        return $this->set('collapsed', $collapsed);
    }



    /**
     * Specify the maximum posts allowed to be selected. Defaults to 0
     *
     * @param  int  $number
     *
     * @return $this
     */
    public function max(int $number): RepeaterField
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
    public function min(int $number): RepeaterField
    {
        return $this->set('min', $number);
    }



    /**
     * Set Defaults
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();
        $this->type('repeater');
        $this->layout('table');
    }

}
