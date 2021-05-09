<?php

namespace GeniePress\Abstracts;

/**
 * Class Condition
 * Used to generate ACF conditions for fields and schemas
 *
 * @package GeniePress\Abstracts
 */
abstract class Condition
{

    /**
     * field name
     *
     * @var string
     */
    protected $fieldName = 'field';

    /**
     * An array of conditions
     *
     * @var array
     */
    protected $conditions = [];

    /**
     * Parse conditions into groups
     *
     * @var array
     */
    protected $group = [];

    /**
     * The current field.
     *
     * @var string|null
     */
    protected $field = '';



    /**
     * constructor.
     *
     * @param  string|null  $field
     */
    public function __construct(string $field = null)
    {
        if ( ! is_null($field)) {
            $this->field = $field;
        }
    }



    /**
     * Static Constructor
     *
     * @param  string  $field
     *
     * @return Condition
     */
    public static function field(string $field): Condition
    {
        return new static($field);
    }



    /**
     * Start a new group with an And clause
     *
     * @param  string  $field
     *
     * @return Condition
     */
    public function and(string $field): Condition
    {
        $this->field = $field;

        return $this;
    }



    /**
     * Check the field contains
     *
     * @param $value
     *
     * @return Condition
     */
    public function contains($value): Condition
    {
        $this->group[] = [
            $this->fieldName => $this->field,
            'operator'       => '==contains',
            'value'          => $value,

        ];

        return $this;
    }



    /**
     * Check the field is empty
     *
     * @return Condition
     */
    public function empty(): Condition
    {
        $this->group[] = [
            $this->fieldName => $this->field,
            'operator'       => '==empty',

        ];

        return $this;
    }



    /**
     * Check the field equals
     *
     * @param $value
     *
     * @return Condition
     */
    public function equals($value): Condition
    {
        $this->group[] = [
            $this->fieldName => $this->field,
            'operator'       => '==',
            'value'          => $value,
        ];

        return $this;
    }



    /**
     * Generate the array condition
     *
     * @return array
     */

    public function generate(): array
    {
        if (count($this->group) > 0) {
            $this->conditions[] = $this->group;
        }

        return $this->conditions;
    }



    /**
     * check the field matches
     *
     * @param $pattern
     *
     * @return Condition
     */
    public function matches($pattern): Condition
    {
        $this->group[] = [
            $this->fieldName => $this->field,
            'operator'       => '==pattern',
            'value'          => $pattern,

        ];

        return $this;
    }



    /**
     * Check the field is not empty
     *
     * @return Condition
     */
    public function notEmpty(): Condition
    {
        $this->group[] = [
            $this->fieldName => $this->field,
            'operator'       => '!=empty',

        ];

        return $this;
    }



    /**
     * Check the field is not equal to
     *
     * @param $value
     *
     * @return Condition
     */
    public function notEquals($value): Condition
    {
        $this->group[] = [
            $this->fieldName => $this->field,
            'operator'       => '!=',
            'value'          => $value,
        ];

        return $this;
    }



    /**
     * Start a new OR group
     *
     * @param $field
     *
     * @return Condition
     */
    public function or($field): Condition
    {
        $this->field        = $field;
        $this->conditions[] = $this->group;
        $this->group        = [];

        return $this;
    }

}
