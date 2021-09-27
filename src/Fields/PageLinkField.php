<?php

namespace GeniePress\Fields;

use GeniePress\Abstracts\Field;

class PageLinkField extends Field
{

    /**
     * Allow Archives
     *
     * @param  bool  $allowArchives
     *
     * @return $this
     */
    public function allowArchives(bool $allowArchives): PageLinkField
    {
        return $this->set('allow_archives', $allowArchives);
    }



    /**
     * Specify if null can be accepted as a value.
     *
     * @param  bool  $allowNull
     *
     * @return $this
     */
    public function allowNull(bool $allowNull): PageLinkField
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
    public function multiple(bool $multiple): PageLinkField
    {
        return $this->set('multiple', $multiple);
    }



    /**
     * Specify an array of post types to filter the available choices. Defaults to ''
     *
     * @param  array  $postObject
     *
     * @return $this
     */
    public function postObject(array $postObject): PageLinkField
    {
        return $this->set('post_type', $postObject);
    }



    /**
     * Specify an array of taxonomies to filter the available choices. Defaults to ''
     *
     * @param  string  $taxonomy
     *
     * @return $this
     */
    public function taxonomy(string $taxonomy): PageLinkField
    {
        return $this->set('taxonomy', $taxonomy);
    }



    /**
     * Set Defaults
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();
        $this->type('page_link');
        $this->metaQuery('NUMERIC');
        $this->postObject(['page']);
    }

}
