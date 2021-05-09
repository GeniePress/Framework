<?php

namespace GeniePress\Fields;

use GeniePress\Abstracts\Field;

class RadioField extends Field
{

    /**
     * Choices for this radio set
     *
     * @param  array  $choices  key=> value paid
     *
     * @return $this
     */
    public function choices(array $choices): RadioField
    {
        return $this->set('choices', $choices);
    }



    /**
     * Specify the layout of the checkbox inputs. Defaults to 'vertical'. Choices of 'vertical' or 'horizontal'
     *
     * @param  string  $layout
     *
     * @return $this
     */
    public function layout(string $layout): RadioField
    {
        return $this->set('layout', $layout);
    }



    /**
     * Showuld other options be allowed?
     *
     * @param  bool  $otherChoices
     *
     * @return $this
     */
    public function otherChoices(bool $otherChoices): RadioField
    {
        return $this->set('other_choice', $otherChoices);
    }



    /**
     * Return Format
     *
     * @param $returnFormat
     *
     * @return $this
     */
    public function returnFormat($returnFormat): RadioField
    {
        return $this->set('return_format', $returnFormat);
    }



    /**
     * Save other choices?
     *
     * @param  bool  $saveOtherChoice
     *
     * @return $this
     */
    public function saveOtherChoice(bool $saveOtherChoice): RadioField
    {
        return $this->set('save_other_choice', $saveOtherChoice);
    }



    /**
     * Set Defaults
     */
    protected function setDefaults()
    {
        parent::setDefaults();
        $this->type('radio');
        $this->layout('vertical');
        $this->returnFormat('array');
    }

}
