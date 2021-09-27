<?php

namespace GeniePress\Abstracts;

use GeniePress\Genie;
use GeniePress\Traits\HasData;
use GeniePress\Utilities\ConvertString;
use GeniePress\Utilities\HookInto;

/**
 * Class Field
 *
 * @package GeniePress\Abstracts
 * @property string $key
 * @property string $type
 * @property string $name
 * @property string $_name
 * @property string $label
 * @property array $hooks
 * @property string $hidden
 * @property string $required
 * @property bool|int|mixed $_prepare
 * @property bool|int|mixed $_valid
 * @property bool|mixed|string $append
 * @property bool|mixed|string $prepend
 * @property bool|mixed|string $instructions
 * @property bool|int|mixed $readonly
 * @property bool|int|mixed $conditional_logic
 * @property bool|mixed|string[] $wrapper
 * @property bool|mixed|string $default_value
 * @property mixed|bool $displayOnly
 * @property mixed|bool $metaQuery
 * @property mixed|bool $override
 */
abstract class Field
{

    use HasData;

    /**
     * Field constructor.
     *
     * @param  string  $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->setDefaults();
    }



    /**
     * Static constructor
     *
     * @param $name
     *
     * @return static
     */
    public static function called($name): Field
    {
        return new static($name);
    }



    /**
     * Add a callback
     *
     * use {$key},{$name},{$type} in the hook name
     *
     * @param  string  $action
     * @param  callable  $callback
     * @param  int  $priority
     *
     * @return Field
     */
    public function addAction(string $action, callable $callback, int $priority = 10): Field
    {
        $hooks = $this->hooks;

        $hooks[] = (object) [
            'type'     => 'action',
            'hook'     => $action,
            'callback' => $callback,
            'priority' => $priority,
        ];

        $this->hooks = $hooks;

        return $this;
    }



    /**
     * use {$key},{$name},{$type} in the filter name
     *
     * @param  string  $filter
     * @param  callable  $callback
     * @param  int  $priority
     *
     * @return Field
     */
    public function addFilter(string $filter, callable $callback, int $priority = 10): Field
    {
        $hooks       = $this->hooks;
        $hooks[]     = (object) [
            'type'     => 'filter',
            'hook'     => $filter,
            'callback' => $callback,
            'priority' => $priority,
        ];
        $this->hooks = $hooks;

        return $this;
    }



    /**
     * Set append string
     *
     * @param  string  $string
     *
     * @return $this
     */
    public function append(string $string): Field
    {
        return $this->set('append', $string);
    }



    /**
     * Field Conditional Logic as an Array
     *
     * @param $conditionalLogic
     *
     * @return $this
     */
    public function conditionalLogic($conditionalLogic): Field
    {
        return $this->set('conditional_logic', $conditionalLogic);
    }



    /**
     * Set the default value for this field.
     *
     * @param $default
     *
     * @return $this
     */
    public function default($default): Field
    {
        return $this->set('default_value', $default);
    }



    /**
     * For use with Genie - Sets this as a display only so it's not attached to objects
     *
     * @param $displayOnly
     *
     * @return $this
     */
    public function displayOnly($displayOnly): Field
    {
        return $this->set('displayOnly', $displayOnly);
    }



    /**
     * Handy shortcut to ACF
     * https://www.advancedcustomfields.com/resources/acf-format_value/
     *
     * callable accepts
     *
     * @param  callable  $callable  Accepts $value, $post_id, $field
     *
     * @return $this
     */
    public function formatValue(callable $callable): Field
    {
        return $this->addFilter('acf/format_value/key={$key}', $callable);
    }



    /**
     * generate the ACF definition for this field
     *
     * @param $parent_key
     *
     * @return array
     */
    public function generate($parent_key): array
    {
        // Allow the defaults to be filtered
        apply_filters(Genie::hookName('field_generate'), $this);
        apply_filters(Genie::hookName('field_generate_'.$this->type), $this);

        $key = $this->key;
        if ( ! $key) {
            $key = $parent_key.'_'.strtolower($this->name);
            $this->set('key', 'field_'.$key);
        }

        if (isset($this->sub_fields)) {
            $subFields = [];
            foreach ($this->sub_fields as $field) {
                $subFields[] = $field->generate($key);
            }
            $this->set('sub_fields', $subFields);
        }

        // Flexible Content
        if (isset($this->layouts)) {
            $subFields = [];
            foreach ($this->layouts as $field) {
                $subFields[] = $field->generate($key);
            }
            $this->set('layouts', $subFields);
        }

        // Handle hooks
        foreach ($this->hooks as $hook) {
            if ($hook->type === 'filter') {
                HookInto::filter($this->parseHookName($hook->hook), $hook->priority)
                    ->run($hook->callback);
            } else {
                HookInto::action($this->parseHookName($hook->hook), $hook->priority)
                    ->run($hook->callback);
            }
        }

        return $this->data;
    }



    /**
     * if this field hidden?
     *
     * @param  bool  $value
     *
     * @return $this
     */
    public function hidden(bool $value): Field
    {
        return $this->set('hidden', $value);
    }



    /**
     * Sets the HTML id
     *
     * @param $id
     *
     * @return $this
     */
    public function id($id): Field
    {
        $this->data['wrapper']['id'] = $id;

        return $this;
    }



    /**
     * Field instructions
     *
     * @param  string  $instructions
     *
     * @return $this
     */
    public function instructions(string $instructions): Field
    {
        return $this->set('instructions', $instructions);
    }



    /**
     * Sets the key for this field
     *
     * @param $key
     *
     * @return $this
     */
    public function key($key): Field
    {
        return $this->set('key', $key);
    }



    /**
     * Sets a label for this field
     *
     * @param $label
     *
     * @return $this
     */
    public function label($label): Field
    {
        return $this->set('label', $label);
    }



    /**
     * Handy shortcut to ACF
     * https://www.advancedcustomfields.com/resources/acf-load_field/
     *
     * @param  callable  $callable  accepts $field
     *
     * @return $this
     */
    public function loadField(callable $callable): Field
    {
        return $this->addFilter('acf/load_field/key={$key}', $callable);
    }



    /**
     * Handy shortcut to ACF
     * https://www.advancedcustomfields.com/resources/acf-load_value/
     *
     * @param  callable  $callable  accepts  $value, $post_id, $field
     *
     * @return $this
     */
    public function loadValue(callable $callable): Field
    {
        return $this->addFilter('acf/load_value/key={$key}', $callable);
    }



    /**
     * Set the metaQuery type
     *
     * @param $metaQuery
     *
     * @return $this
     */
    public function metaQuery($metaQuery): Field
    {
        return $this->set('meta_query', $metaQuery);
    }



    /**
     * Allows overriding WordPress fields
     *
     * @param $field
     *
     * @return $this
     */
    public function override($field): Field
    {
        return $this->set('override', $field);
    }



    /**
     * Handy shortcut to ACF
     * https://www.advancedcustomfields.com/resources/acf-prepare_field/
     *
     * @param  callable  $callable  accepts $field
     *
     * @return $this
     */
    public function prepareField(callable $callable): Field
    {
        return $this->addFilter('acf/prepare_field/key={$key}', $callable);
    }



    /**
     * Set the Prefix
     *
     * @param  string  $string
     *
     * @return $this
     */
    public function prepend(string $string): Field
    {
        return $this->set('prepend', $string);
    }



    /**
     * Sets a label for this field
     *
     * @param  bool  $readOnly
     *
     * @return $this
     */
    public function readOnly(bool $readOnly): Field
    {
        return $this->set('readonly', $readOnly);
    }



    /**
     * Handy shortcut to ACF Render Field
     * https://www.advancedcustomfields.com/resources/acf-render_field/
     *
     * @param  callable  $callable  accepts $field
     *
     * @return $this
     */
    public function renderField(callable $callable): Field
    {
        return $this->addAction('acf/render_field/key={$key}', $callable);
    }



    /**
     * Is this field required ?
     *
     * @param  bool  $value
     *
     * @return $this
     */
    public function required(bool $value): Field
    {
        return $this->set('required', $value);
    }



    /**
     * Set a value
     *
     * @param $var
     * @param $value
     *
     * @return $this
     */
    public function set($var, $value): Field
    {
        $this->$var = $value;

        return $this;
    }



    /**
     * Field condition
     *
     * @param  Condition  $condition
     *
     * @return $this
     */
    public function shown(Condition $condition): Field
    {
        return $this->set('conditions', $condition->generate());
    }



    /**
     * Handy shortcut to ACF
     * https://www.advancedcustomfields.com/resources/acf-update_field/
     *
     * @param  callable  $callable  accepts  $field
     *
     * @return $this
     */
    public function updateField(callable $callable): Field
    {
        return $this->addFilter('acf/update_field/key={$key}', $callable);
    }



    /**
     * Handy shortcut to ACF
     * https://www.advancedcustomfields.com/resources/acf-update_value/
     *
     * @param  callable  $callable  accepts $value, $post_id, $field, $original
     *
     * @return $this
     */
    public function updateValue(callable $callable): Field
    {
        return $this->addFilter('acf/update_value/key={$key}', $callable);
    }



    /**
     * Handy shortcut to ACF
     * https://www.advancedcustomfields.com/resources/acf-validate_attachment/
     *
     * @param  callable  $callable  accepts $errors, $file, $attachment, $field, $context
     *
     * @return $this
     */
    public function validateAttachment(callable $callable): Field
    {
        return $this->addFilter('acf/validate_attachment/key={$key}', $callable);
    }



    /**
     * Handy shortcut to ACF
     * https://www.advancedcustomfields.com/resources/acf-validate_value/
     *
     * @param  callable  $callable  accepts $valid, $value, $field, $input_name
     *
     * @return $this
     */
    public function validateValue(callable $callable): Field
    {
        return $this->addFilter('acf/validate_value/key={$key}', $callable);
    }



    /**
     * Set the wrapper Class
     *
     * @param $class
     *
     * @return $this
     */
    public function wrapperClass($class): Field
    {
        $this->data['wrapper']['class'] = $class;

        return $this;
    }



    /**
     * Sets the wrapper width in %
     *
     * @param $width
     *
     * @return $this
     */
    public function wrapperWidth($width): Field
    {
        $this->data['wrapper']['width'] = $width;

        return $this;
    }



    /**
     * Now that we have generated the key, we can return a property hook
     *
     * @param $name
     *
     * @return string|string[]
     */
    protected function parseHookName($name)
    {
        $find    = [
            '{$key}',
            '{$name}',
            '{$type}',
        ];
        $replace = [
            $this->key,
            $this->name,
            $this->type,
        ];

        return str_replace($find, $replace, $name);
    }



    /**
     * Set defaults for all Fields
     */
    protected function setDefaults(): void
    {
        // hack - can't seem to figure out how ACF adds _name to locally imported groups.
        // This is needed by the acf_format_value function
        $this->_name    = $this->name;
        $this->_prepare = 0;
        $this->_valid   = 0;
        $this->hooks    = [];

        $this->type('text');
        $this->key('');

        // The label defaults to the field name
        $this->label((string) ConvertString::from($this->name)->toTitleCase());

        //  This will be used later when doing smart filtering
        $this->metaQuery('CHAR');

        // (int) Whether the field value is required. Defaults to 0
        $this->required(0);
        $this->append('');
        $this->prepend('');
        $this->instructions('');
        $this->readOnly(0);

        // (mixed) Conditionally hide or show this field based on other field's values.
        // Best to use the ACF UI and export to understand the array structure. Defaults to 0
        $this->conditionalLogic(0);

        // Styling
        $this->wrapperWidth('');
        $this->wrapperClass('');
        $this->id('');
        $this->default('');
        $this->readOnly(false);

        // Genie Defaults
        $this->hidden(false);

        // Does this field not have any input? Used for Tab & Message
        $this->displayOnly(false);

        // WordPress post field to override on save (e.g. post_title)
        $this->override(false);
    }



    /**
     * Set the field type
     *
     * @param $type
     *
     * @return $this
     */
    protected function type($type): Field
    {
        return $this->set('type', $type);
    }

}
