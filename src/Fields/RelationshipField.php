<?php

namespace GeniePress\Fields;

use GeniePress\Abstracts\Field;

class RelationshipField extends Field
{

    /**
     * Specify the visual elements for each post. Choices of 'featured_image' (Featured image icon)
     *
     * @param  array  $elements
     *
     * @return $this
     */
    public function elements(array $elements): RelationshipField
    {
        return $this->set('elements', $elements);
    }



    /**
     *  Specify the available filters used to search for posts. Choices of 'search' (Search input), 'post_type' (Post type select) and 'taxonomy' (Taxonomy select)
     *
     * @param  array  $filters
     *
     * @return $this
     */
    public function filters(array $filters): RelationshipField
    {
        return $this->set('filters', $filters);
    }



    /**
     * Specify the maximum posts allowed to be selected. Defaults to 0
     *
     * @param  int  $number
     *
     * @return $this
     */
    public function max(int $number): RelationshipField
    {
        return $this->set('max', $number);
    }



    /**
     * Specify the minimum posts required to be selected. Defaults to 0
     *
     * @param  int  $number
     *
     * @return $this
     */
    public function min(int $number): RelationshipField
    {
        return $this->set('min', $number);
    }



    /**
     * Specify an array of post types to filter the available choices. Defaults to ''
     *
     * @param  array  $postObject
     *
     * @return $this
     */
    public function postObject(array $postObject): RelationshipField
    {
        $this->set('post_type', $postObject);
        $this->returnFormat('id');

        return $this;
    }



    /**
     * Specify the type of value returned by get_field(). Defaults to 'object'.
     * Choices of 'object' (Post object) or 'id' (Post ID)
     *
     * @param  string  $returnFormat  object|id
     *
     * @return $this
     */

    public function returnFormat(string $returnFormat): RelationshipField
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
    public function taxonomy(string $taxonomy): RelationshipField
    {
        return $this->set('taxonomy', $taxonomy);
    }



    /**
     * Set Defaults
     */
    protected function setDefaults()
    {
        parent::setDefaults();
        $this->type('relationship');
        $this->filters(['search']);
        $this->elements(['featured_image']);
    }

}
