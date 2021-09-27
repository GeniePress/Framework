<?php

namespace GeniePress\Fields;

use GeniePress\Abstracts\Field;

/**
 * Class CheckboxField
 *
 * @package GeniePress\Fields
 */
class CheckboxField extends Field
{

    /**
     * Specify choices for the checkbox
     *
     * @param  array  $choices
     *
     * @return $this
     */
    public function choices(array $choices): CheckboxField
    {
        return $this->set('choices', $choices);
    }



    /**
     * Specify the layout of the checkbox inputs. Defaults to 'vertical'. Choices of 'vertical' or 'horizontal
     *
     * @param  string  $layout  vertical|horizontal
     *
     * @return $this
     */
    public function layout(string $layout): CheckboxField
    {
        return $this->set('layout', $layout);
    }



    /**
     *Text shown alongside the checkbox
     *
     * @param  string  $message
     *
     * @return $this
     */
    public function message(string $message): CheckboxField
    {
        return $this->set('message', $message);
    }



    /**
     * Specify the return format
     *
     * @param  string  $returnFormat  array|value
     *
     * @return $this
     */
    public function returnFormat(string $returnFormat): CheckboxField
    {
        return $this->set('return_format', $returnFormat);
    }



    /**
     * Specify if there should be a "toggle all" option
     *
     * @param  bool  $toggle
     *
     * @return $this
     */
    public function toggle(bool $toggle): CheckboxField
    {
        return $this->set('toggle', $toggle);
    }



    /**
     * Set Defaults
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();
        $this->type('checkbox');
        $this->layout('vertical');
        $this->returnFormat('array');
    }

}
