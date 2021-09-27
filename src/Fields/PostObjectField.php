<?php

namespace GeniePress\Fields;

use GeniePress\Abstracts\Field;

class PostObjectField extends Field
{

    /**
     * Specify if null can be accepted as a value.
     *
     * @param  bool  $allowNull
     *
     * @return $this
     */
    public function allowNull(bool $allowNull): PostObjectField
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
    public function multiple(bool $multiple): PostObjectField
    {
        return $this->set('multiple', $multiple);
    }



    /**
     * Specify an array of post types to filter the available choices. Defaults to ''
     *
     * @param  array|string  $postObject
     *
     * @return $this
     */
    public function postObject($postObject): PostObjectField
    {
        if ( ! is_array($postObject)) {
            $postObject = [$postObject];
        }

        return $this->set('post_type', $postObject);
    }



    /**
     * Specify the type of value returned by get_field(). Defaults to 'object'. Choices of 'object' (Post object) or 'id' (Post ID)
     *
     * @param  string  $returnFormat  object|id
     *
     * @return $this
     */
    public function returnFormat(string $returnFormat): PostObjectField
    {
        return $this->set('return_format', $returnFormat);
    }



    /**
     * Specify an array of taxonomies to filter the available choices. Defaults to ''
     *
     * @param  string  $taxonomy
     *
     * @return $this
     */
    public function taxonomy(string $taxonomy): PostObjectField
    {
        return $this->set('taxonomy', $taxonomy);
    }



    /**
     * Set defaults
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();
        $this->type('post_object');
        $this->metaQuery('NUMERIC');
        $this->returnFormat('id');
        $this->allowNull(false);
        $this->multiple(false);
    }

}
