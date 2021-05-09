<?php

namespace GeniePress\Fields;

use GeniePress\Abstracts\Field;

class MessageField extends Field
{

    /**
     * Should HTML be escaped ?
     *
     * @param  bool  $escape
     *
     * @return $this
     */
    public function escapeHTML(bool $escape): MessageField
    {
        return $this->set('esc_html', $escape);
    }



    /**
     *Text shown
     *
     * @param  string  $message
     *
     * @return $this
     */
    public function message(string $message): MessageField
    {
        return $this->set('message', $message);
    }



    /**
     * Decides how to render new lines. Detauls to 'wpautop'. Choices of 'wpautop' (Automatically add paragraphs), 'br' (Automatically add <br>) or '' (No Formatting)
     *
     * @param $newLines  string wpautop|br|ni;;
     *
     * @return $this
     */
    public function newLines(string $newLines): MessageField
    {
        return $this->set('new_lines', $newLines);
    }



    /**
     * Set Defaults
     */
    protected function setDefaults()
    {
        parent::setDefaults();
        $this->type('message');
        $this->displayOnly(true);
        $this->newLines('wpautop');
        $this->escapeHTML(false);
    }

}
