<?php

namespace GeniePress\Fields;

use GeniePress\Abstracts\Field;

class DateField extends Field
{

    /**
     * Sets the display Format (PHP Date format)
     *
     * @param  string  $format
     *
     * @return $this
     */
    public function displayFormat(string $format): DateField
    {
        return $this->set('display_format', $format);
    }



    /**
     * Set the 1st day of the week
     *
     * @param  int  $day
     *
     * @return $this
     */
    public function firstDay(int $day): DateField
    {
        return $this->set('first_day', $day);
    }



    /**
     * Specify the return format (PHP date format)
     *
     * @param  string  $format
     *
     * @return $this
     */
    public function returnFormat(string $format): DateField
    {
        return $this->set('return_format', $format);
    }



    /**
     * Set Defaults
     */
    protected function setDefaults()
    {
        parent::setDefaults();

        $this->type('date_picker');
        $this->metaQuery('DATE');
        $this->displayFormat('d/m/Y');
        $this->returnFormat('Y-m-d');
    }

}
