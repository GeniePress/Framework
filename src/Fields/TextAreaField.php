<?php

namespace GeniePress\Fields;

class TextAreaField extends TextField
{

    /**
     * Decides how to render new lines. Detauls to 'wpautop'. Choices of 'wpautop' (Automatically add paragraphs), 'br' (Automatically add <br>) or '' (No Formatting)
     *
     * @param $newLines  string wpautop|br|ni;;
     *
     * @return $this
     */
    public function newLines(string $newLines): TextAreaField
    {
        return $this->set('new_lines', $newLines);
    }



    /**
     * The number of rows for this input
     *
     * @param  int  $rows
     *
     * @return $this
     */
    public function rows(int $rows): TextAreaField
    {
        return $this->set('rows', $rows);
    }



    /**
     * Set Defaults
     */
    protected function setDefaults()
    {
        parent::setDefaults();
        $this->type('textarea');
        $this->newLines('');
    }

}
