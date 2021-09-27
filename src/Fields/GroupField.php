<?php

namespace GeniePress\Fields;

use GeniePress\Abstracts\Field;

/**
 * Class GroupField
 *
 * @package GeniePress\Fields
 * @property array $sub_fields
 */
class GroupField extends Field
{

    /**
     * layout
     *
     * @param  string  $layout  table|block|row
     *
     * @return $this
     */
    public function layout(string $layout): GroupField
    {
        return $this->set('layout', $layout);
    }



    /**
     * Add Fields
     *
     * @param  array  $fields
     *
     * @return $this
     */
    public function withFields(array $fields): GroupField
    {
        return $this->set('sub_fields', array_merge($this->sub_fields, $fields));
    }



    /**
     * Set Defaults
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();
        $this->type('group');
        $this->layout('row');
        $this->set('sub_fields', []);
    }

}
