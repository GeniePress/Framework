<?php

namespace GeniePress\Fields;

use GeniePress\Abstracts\Field;

class SelectField extends Field
{

    /**
     * Should the values be loaded by Ajax?
     *
     * @param  bool  $ajax
     *
     * @return $this
     */
    public function ajax(bool $ajax): SelectField
    {
        return $this->set('ajax', $ajax);
    }



    /**
     * Allow no value to be selected
     *
     * @param $allowNull
     *
     * @return $this
     */
    public function allowNull($allowNull): SelectField
    {
        return $this->set('allow_null', $allowNull);
    }



    /**
     * Choices for this select dropdown
     *
     * @param  array  $choices  key=> value paid
     *
     * @return $this
     */
    public function choices(array $choices): SelectField
    {
        return $this->set('choices', $choices);
    }



    /**
     * select multiple values?
     *
     * @param  bool  $multiple
     *
     * @return $this
     */
    public function multiple(bool $multiple): SelectField
    {
        return $this->set('multiple', $multiple);
    }



    /**
     * Return Format
     *
     * @param  string  $returnValue
     *
     * @return $this
     */
    public function returnFormat(string $returnValue): SelectField
    {
        return $this->set('return_format', $returnValue);
    }



    /**
     * use an improved UI ?
     *
     * @param  bool  $ui
     *
     * @return $this
     */
    public function ui(bool $ui): SelectField
    {
        return $this->set('ui', $ui);
    }



    /**
     * Set Defaults
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();
        $this->type('select');
        $this->allowNull(true);
        $this->ui(true);
        $this->returnFormat('array');
    }

}
