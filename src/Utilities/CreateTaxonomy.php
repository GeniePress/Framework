<?php

namespace GeniePress\Utilities;

use GeniePress\Genie;

/**
 * Class CreateTaxonomy
 * Wrapper around the register_taxonomy function
 *
 * @package GeniePress\Utilities
 */
class CreateTaxonomy
{

    /**
     * Objects to attach this taxonomy to
     *
     * @var array
     */
    protected $attachTo = [];

    /**
     * see https://codex.WordPress.org/Function_Reference/register_taxonomy
     *
     * @var array
     */
    protected $definition = [
        'label'                 => '',
        'labels'                => [
            'name'                       => '',
            'singular_name'              => '',
            'menu_name'                  => '',
            'all_items'                  => '',
            'edit_item'                  => '',
            'view_item'                  => '',
            'update_item'                => '',
            'add_new_item'               => '',
            'new_item_name'              => '',
            'parent_item'                => '',
            'parent_item_colon'          => '',
            'search_items'               => '',
            'popular_items'              => '',
            'separate_items_with_commas' => '',
            'add_or_remove_items'        => '',
            'choose_from_most_used'      => '',
            'not_found'                  => '',
            'back_to_items'              => '',
        ],
        'public'                => false,
        'publicly_queryable'    => false,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'show_in_nav_menus'     => false,
        'show_in_rest'          => false,
        'show_tagcloud'         => false,
        'meta_box_cb'           => null,
        'show_admin_column'     => true,
        'description'           => '',
        'hierarchical'          => true,
        'update_count_callback' => '',
        'query_var'             => '',
        'rewrite'               => true,
        'sort'                  => '',

    ];

    /**
     * Taxonomy slug
     *
     * @var string
     */
    protected $taxonomy;



    /**
     * CreateCustomPostType constructor.
     *
     * @param  string  $name  Name
     */
    public function __construct(string $name)
    {
        $string = ConvertString::from($name);

        // default to the snaked case of teh singular
        // $name = 'Product Categories' turns into 'product_category'
        $this->taxonomy = $string->toSingular()->toSnakeCase();

        $singular = (string) $string->toTitleCase()->toSingular();
        $plural   = (string) $string->toPlural();

        $this->setLabels([

            'name'                       => $plural,
            'singular_name'              => $singular,
            'menu_name'                  => $plural,
            'all_items'                  => 'All '.$plural,
            'edit_item'                  => 'Edit '.$singular,
            'view_item'                  => 'View '.$singular,
            'update_item'                => 'Update '.$singular,
            'add_new_item'               => 'Add New '.$singular,
            'new_item_name'              => 'New '.$singular.' Name',
            'parent_item'                => 'Parent '.$singular,
            'parent_item_colon'          => 'Parent '.$singular.':',
            'search_items'               => 'Search '.$plural,
            'popular_items'              => 'Popular '.$plural,
            'separate_items_with_commas' => 'Separate '.$plural.' with commas',
            'add_or_remove_items'        => 'Add or remove '.$plural,
            'choose_from_most_used'      => 'Choose from the most used '.$plural,
            'not_found'                  => 'No '.$plural.' found',
            'back_to_items'              => '← Back to '.$plural,
        ]);

        $this->set('label', $plural);
        $this->set('description', $name);
    }



    /**
     * static constructor
     *
     * @param $name
     *
     * @return CreateTaxonomy
     */
    public static function called($name): CreateTaxonomy
    {
        return new static($name);
    }



    /**
     * Attach to a post type
     *
     * @param $object
     *
     * @return $this
     */
    public function attachTo($object): CreateTaxonomy
    {
        $this->attachTo[] = $object;

        return $this;
    }



    /**
     * get the taxonomy definition;
     * @return array
     */
    public function getDefinition(): array
    {
        return $this->definition;
    }



    /**
     * Get the taxonomy name
     *
     * @return string
     */
    public function getTaxonomy(): string
    {
        return $this->taxonomy;
    }



    /**
     * Sets this taxonomy as being hidden
     *
     * @return $this
     */
    public function hidden(): CreateTaxonomy
    {
        $this->set('show_ui', false);
        $this->set('show_in_nav_menus', false);

        return $this;
    }



    /**
     * Register the Taxonomy
     *
     * @param  int  $sequence  The priority
     * @param  bool  $onActivation  Should this be registered on activation as well? defaults to true
     */
    public function register(int $sequence = 20, bool $onActivation = true): void
    {
        $hook = HookInto::action('init', $sequence);
        if ($onActivation) {
            $hook->orAction(Genie::hookName('activation'), 1);
        }
        $hook->run(function () {
            $attachTo = empty($this->attachTo) ? null : $this->attachTo;

            register_taxonomy($this->taxonomy, $attachTo, $this->definition);
        });
    }



    /**
     * Set a property
     *
     * @param $attribute
     * @param $value
     *
     * @return $this
     */
    public function set($attribute, $value): CreateTaxonomy
    {
        $this->definition[$attribute] = $value;

        return $this;
    }



    /**
     * Set a label
     *
     * @param $label
     * @param $name
     *
     * @return $this
     */
    public function setLabel($label, $name): CreateTaxonomy
    {
        $this->definition['labels'][$label] = $name;

        return $this;
    }



    /**
     * Set labels
     *
     * @param  array  $labels
     *
     * @return $this
     */
    public function setLabels(array $labels): CreateTaxonomy
    {
        foreach ($labels as $label => $value) {
            $this->setLabel($label, $value);
        }

        return $this;
    }



    /**
     * Set the taxonomy name
     *
     * @param  string  $taxonomy
     *
     * @return $this
     */
    public function setTaxonomy(string $taxonomy): CreateTaxonomy
    {
        $this->taxonomy = $taxonomy;

        return $this;
    }

}
