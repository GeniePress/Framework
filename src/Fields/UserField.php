<?php

namespace GeniePress\Fields;

use GeniePress\Abstracts\Field;

class UserField extends Field
{

    /**
     * Allow no value to be selected
     *
     * @param  bool  $allowNull
     *
     * @return $this
     */
    public function allowNull(bool $allowNull): UserField
    {
        return $this->set('allow_null', $allowNull);
    }



    /**
     * Allow multiple values to be selected
     *
     * @param  bool  $multiple
     *
     * @return $this
     */
    public function multiple(bool $multiple): UserField
    {
        return $this->set('multiple', $multiple);
    }



    /**
     * Limit to WordPress Role
     *
     * @param  string  $role
     *
     * @return $this
     */
    public function role(string $role): UserField
    {
        return $this->set('role', $role);
    }



    /**
     * Set Defaults
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();
        $this->type('user');
        $this->metaQuery('NUMERIC');
    }

}
