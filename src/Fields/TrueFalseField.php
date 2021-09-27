<?php

namespace GeniePress\Fields;

use GeniePress\Abstracts\Field;

class TrueFalseField extends Field
{

    /**
     * Text shown alongside the field
     *
     * @param  string  $message
     *
     * @return $this
     */
    public function message(string $message): TrueFalseField
    {
        return $this->set('message', $message);
    }



    /**
     * text to show on the switch in the off position
     *
     * @param  string  $text
     *
     * @return $this
     */
    public function offText(string $text): TrueFalseField
    {
        return $this->set('ui_off_text', $text);
    }



    /**
     * Text to show on the switch in the on position
     *
     * @param  string  $text
     *
     * @return $this
     */
    public function onText(string $text): TrueFalseField
    {
        return $this->set('ui_on_text', $text);
    }



    /**
     * Use a UI switch?
     *
     * @param  bool  $ui
     *
     * @return $this
     */
    public function ui(bool $ui): TrueFalseField
    {
        return $this->set('ui', $ui);
    }



    /**
     * Set Defaults
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();
        $this->type('true_false');
        $this->metaQuery('NUMERIC');
        $this->ui(true);
        $this->onText('Yes');
        $this->offText('No');
    }

}
