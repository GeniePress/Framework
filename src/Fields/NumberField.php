<?php

namespace GeniePress\Fields;

class NumberField extends TextField
{

    /**
     * Maximum value for this field
     *
     * @param  int  $max
     *
     * @return $this
     */
    public function max(int $max): NumberField
    {
        return $this->set('max', $max);
    }



    /**
     * Minimum value for this field
     *
     * @param  int  $min
     *
     * @return $this
     */
    public function min(int $min): NumberField
    {
        return $this->set('min', $min);
    }



    /**
     * Increment Step
     *
     * @param  int  $step
     *
     * @return $this
     */
    public function step(int $step): NumberField
    {
        return $this->set('step', $step);
    }



    /**
     * Set Defaults
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();
        $this->type('number');
        $this->metaQuery('NUMERIC');
    }

}
