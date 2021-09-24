<?php

namespace GeniePress\Utilities;

use GeniePress\Abstracts\Condition;
use GeniePress\Abstracts\Field;
use GeniePress\Debug;
use GeniePress\Genie;
use GeniePress\Registry;

/**
 * Class CreateSchema
 * Powerful wrapper around ACF.
 *  - Avoids having to create schema in the backend
 *  - Dynamically create schema
 *
 * @package GeniePress
 */
class CreateSchema
{

    private $key;

    private $title;

    private $menu_order = 0;

    private $location;

    private $position = 'normal';

    private $style = 'default';

    private $label_placement = 'top';

    private $instruction_placement = 'label';

    private $hide_on_screen = [];

    private $fields = [];

    private $attachTo = null;



    /**
     * CreateSchema constructor.
     *
     * @param $title
     */
    public function __construct($title)
    {
        $this->key   = 'group_'.sanitize_title($title);
        $this->title = $title;
    }



    /**
     * Static constructor
     *
     * @param $name
     *
     * @return CreateSchema
     */

    public static function called($name): CreateSchema
    {
        return new static($name);
    }



    /**
     * Attach this schema to a post type - effectively defining Fields
     *
     * @param $class
     *
     * @return $this
     */
    public function attachTo($class): CreateSchema
    {
        $this->attachTo = $class;

        return $this;
    }



    /**
     * Helper function
     */
    function dump()
    {
        Debug::dd($this->generateSchemaArray());
    }



    /**
     * An Array of Wordpress elements to hide on Screen
     * 'permalink',
     * 'the_content',
     * 'excerpt',
     * 'discussion',
     * 'comments',
     * 'revisions',
     * 'slug',
     * 'author',
     * 'format',
     * 'page_attributes',
     * 'featured_image',
     * 'categories',
     * 'tags',
     * 'send-trackbacks',
     *
     * @param  array  $hide_on_screen
     *
     * @return $this
     */
    public function hideOnScreen(array $hide_on_screen): CreateSchema
    {
        $this->hide_on_screen = $hide_on_screen;

        return $this;
    }



    /**
     * Instruction Placement
     *
     * @param  string  $instruction_placement  label|field
     *
     * @return $this
     */
    public function instructionPlacement(string $instruction_placement): CreateSchema
    {
        $this->instruction_placement = $instruction_placement;

        return $this;
    }



    /**
     * label placement
     *
     * @param  string  $label_placement  top|left
     *
     * @return $this
     */
    public function labelPlacement(string $label_placement): CreateSchema
    {
        $this->label_placement = $label_placement;

        return $this;
    }



    /**
     * Menu Order
     *
     * @param  int  $menuOrder
     *
     * @return $this
     */
    public function menuOrder(int $menuOrder): CreateSchema
    {
        $this->menu_order = $menuOrder;

        return $this;
    }



    /**
     * Position
     *
     * @param  string  $position  acf_after_title|normal|side
     *
     * @return $this
     */
    public function position(string $position): CreateSchema
    {
        $this->position = $position;

        return $this;
    }



    /**
     * Generate and register the schema with ACF
     *
     * @param  int  $sequence  The activation hook priority
     * @param  bool  $onActivation  Should this be registered on activation as well? defaults to true
     */
    function register(int $sequence = 20, bool $onActivation = true)
    {
        $hook = HookInto::action('init', $sequence);
        if ($onActivation) {
            $hook->orAction(Genie::hookName('activation'), 1);
        }
        $hook->run(function () {
            $schema = $this->return();
            if (function_exists('acf_add_local_field_group')) {
                acf_add_local_field_group($schema);
            }
        });
    }



    /**
     * Generate the Schema Array
     *
     * @return array
     */
    function return(): array
    {
        return $this->generateSchemaArray();
    }



    /**
     * Accepts a condition where to show this schema
     *
     * @param  Condition  $condition
     *
     * @return $this
     */
    public function shown(Condition $condition): CreateSchema
    {
        $this->location = $condition->generate();

        return $this;
    }



    /**
     * Sets the field styles
     *
     * @param  string  $style  default|seamless
     *
     * @return $this
     */
    public function style(string $style): CreateSchema
    {
        $this->style = $style;

        return $this;
    }



    /**
     * Add a single field
     *
     * @param  Field  $field
     *
     * @return $this
     */
    public function withField(Field $field): CreateSchema
    {
        $this->fields[] = $field;

        return $this;
    }



    /**
     * Field definitions. Required.
     *
     * @param  Field[]  $fields
     *
     * @return $this
     */
    public function withFields(array $fields): CreateSchema
    {
        $this->fields = array_merge($this->fields, $fields);

        return $this;
    }



    /**
     * Go through the field definitions and convert a name
     * to a acf key where needed.
     * We come at the 1st level.  We need to be careful of sub_fields
     * having the same name
     *
     * @param $field
     * @param $fields
     *
     * @return mixed
     */
    protected function convertNameToKey($field, $fields)
    {
        if (isset($field['conditions'])) {
            foreach ($field['conditions'] as &$condition) {
                foreach ($condition as &$statement) {
                    if (isset($statement['field'])) {
                        $name = $statement['field'];
                        // only do this if it's not a key
                        if (substr($name, 0, 6) != 'field_') {
                            $statement['field'] = $this->findNameInFieldsAndReturnKey($name, $fields);
                        }
                    }
                }
            }
        }
        if (isset($field['sub_fields'])) {
            foreach ($field['sub_fields'] as &$subfield) {
                $subfield = $this->convertNameToKey($subfield, $fields);
            }
        }

        return $field;
    }



    /**
     * Recursive function to parse sub_fields looking for $name
     *
     * @param $name
     * @param $fields
     *
     * @return mixed
     */
    protected function findNameInFieldsAndReturnKey($name, $fields)
    {
        foreach ($fields as $field) {
            if ($field['name'] === $name) {
                return $field['key'];
            }
            if (isset($field['sub_fields'])) {
                $found = $this->findNameInFieldsAndReturnKey($name, $field['sub_fields']);
                if ($found) {
                    return $found;
                }
            }
        }

        return false;
    }



    /**
     * Create the schema for ACF, and attach if necessary.
     *
     * @return array
     */
    protected function generateSchemaArray(): array
    {
        $fields = [];
        foreach ($this->fields as $field) {
            $fields[] = $field->generate(sanitize_title($this->title));
        }

        foreach ($fields as &$field) {
            $field = $this->convertNameToKey($field, $fields);
        }

        $schema = [
            'key'                   => $this->key,
            'title'                 => $this->title,
            'menu_order'            => $this->menu_order,
            'fields'                => $fields,
            'location'              => $this->location,
            'position'              => $this->position,
            'style'                 => $this->style,
            'label_placement'       => $this->label_placement,
            'instruction_placement' => $this->instruction_placement,
            'hide_on_screen'        => $this->hide_on_screen,
        ];

        if ($this->attachTo) {
            /**
             * Parse the schema and build a map of the field name / keys
             * We will use this later when saving data.
             * Problem with setting static::$schema here so we use the registry instead.
             * https://stackoverflow.com/questions/4577187/php-5-3-late-static-binding-doesnt-work-for-properties-when-defined-in-parent
             * This is called from CreateSchema
             *
             * @param $schema
             */

            $registryFields  = Registry::get('fields');
            $registrySchemas = Registry::get('schemas');

            if ( ! $registryFields) {
                $registryFields = [];
            }
            if ( ! $registrySchemas) {
                $registrySchemas = [];
            }

            // modify the schema so we get index arrays
            $level1Fields = [];
            foreach ($schema['fields'] as $level1Field) {
                $level1Fields[$level1Field['name']] = $level1Field;
            }

            $registryFields[$this->attachTo]  = $level1Fields;
            $registrySchemas[$this->attachTo] = $schema;

            Registry::set('fields', $registryFields);
            Registry::set('schemas', $registrySchemas);
        }

        return $schema;
    }

}
