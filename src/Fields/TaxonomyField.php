<?php

namespace GeniePress\Fields;

use GeniePress\Abstracts\Field;

class TaxonomyField extends Field
{

    /**
     * Specify if terms added should be added to WordPress
     *
     * @param  bool  $addTerms
     *
     * @return $this
     */
    public function addTerms(bool $addTerms): TaxonomyField
    {
        return $this->set('add_term', $addTerms);
    }



    /**
     * Allow no value to be selected
     *
     * @param  bool  $allowNull
     *
     * @return $this
     */
    public function allowNull(bool $allowNull): TaxonomyField
    {
        return $this->set('allow_null', $allowNull);
    }



    /**
     * Specify the appearance of the taxonomy field. Defaults to 'checkbox'.
     * Choices of 'checkbox' (Checkbox inputs), 'multi_select' (Select field - multiple),
     * 'radio' (Radio inputs) or 'select' (Select field)
     *
     * @param  string  $type  checkbox|multi_select|radio|select
     *
     * @return $this
     */
    public function fieldType(string $type): TaxonomyField
    {
        return $this->set('field_type', $type);
    }



    /**s
     * Load terms from the post?
     *
     * @param  bool  $loadTerms
     *
     * @return $this
     */
    public function loadTerms(bool $loadTerms): TaxonomyField
    {
        return $this->set('load_terms', $loadTerms);
    }



    /**
     * Specify the type of value returned by get_field(). Defaults to 'id'.
     * Choices of 'object' (Term object) or 'id' (Term ID)
     *
     * @param  string  $returnFormat  object|id
     *
     * @return $this
     */

    public function returnFormat(string $returnFormat): TaxonomyField
    {
        return $this->set('return_format', $returnFormat);
    }



    /**
     * Save terms to the post?
     *
     * @param  bool  $saveTerms
     *
     * @return $this
     */
    public function saveTerms(bool $saveTerms): TaxonomyField
    {
        return $this->set('save_terms', $saveTerms);
    }



    /**
     * Specify the taxonomy to select terms from. Defaults to 'category'
     *
     * @param  string  $taxonomy
     *
     * @return $this
     */
    public function taxonomy(string $taxonomy): TaxonomyField
    {
        return $this->set('taxonomy', $taxonomy);
    }



    /**
     * Set Defaults
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();
        $this->type('taxonomy');
        $this->fieldType('select');
    }

}
