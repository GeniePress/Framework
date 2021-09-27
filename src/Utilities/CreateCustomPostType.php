<?php

namespace GeniePress\Utilities;

/**
 * Class CreateCustomPostType
 * A handy wrapper around register_post_type
 *
 * @package GeniePress\Utilities
 */
class CreateCustomPostType
{

    /**
     * see https://codex.WordPress.org/Function_Reference/register_post_type
     *
     * @var array
     */
    protected $definition = [
        'label'               => '',
        'labels'              => [
            "add_new"               => "Add New",
            "not_found"             => "Not found",
            "not_found_in_trash"    => "Not found in Trash",
            "featured_image"        => "Featured Image",
            "set_featured_image"    => "Set featured image",
            "remove_featured_image" => "Remove featured image",
            "use_featured_image"    => "Use as featured image",
        ],
        'description'         => '',
        'public'              => false,
        'menu_icon'           => '',
        'supports'            => ['title', 'thumbnail', 'editor'],
        'taxonomies'          => [],
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 20,
        'show_in_admin_bar'   => true,
        'show_in_nav_menus'   => false,
        'can_export'          => true,
        'has_archive'         => true,
        'hierarchical'        => false,
        'rewrite'             => true,
        'exclude_from_search' => true,
        'show_in_rest'        => true,
        'publicly_queryable'  => false,
        'query_var'           => '',
        'capability_type'     => 'post',

    ];

    /**
     * Post Type
     *
     * @var string
     */
    protected $postType;



    /**
     * CreateCustomPostType constructor.
     *
     * @param  string  $postType  Post Type
     * @param  string  $singular
     * @param  string  $plural
     */
    public function __construct(string $postType, string $singular = '', string $plural = '')
    {
        $string = ConvertString::from($postType);

        $this->postType = $postType;

        if ( ! $singular) {
            $singular = (string) $string->toTitleCase()->toSingular();
        }
        if ( ! $plural) {
            $plural = (string) $string->toPlural();
        }

        $this->setLabels([
            "name"                  => $plural,
            "singular_name"         => $singular,
            "menu_name"             => $plural,
            "name_admin_bar"        => $singular,
            "archives"              => "$plural Archives",
            "attributes"            => "$singular Attributes",
            "parent_item_colon"     => "Parent $singular:",
            "all_items"             => "All $plural",
            "add_new_item"          => "Add New $singular",
            "new_item"              => "New $singular",
            "edit_item"             => "Edit $singular",
            "update_item"           => "Update $singular",
            "view_item"             => "View $singular",
            "view_items"            => "View $plural",
            "search_items"          => "Search $singular",
            "insert_into_item"      => "Insert into $singular",
            "uploaded_to_this_item" => "Uploaded to this $singular",
            "items_list"            => "$plural list",
            "items_list_navigation" => "$plural list navigation",
            "filter_items_list"     => "Filter $plural list",
        ]);

        $this->set('label', $plural);
        $this->set('description', $postType);
    }



    /**
     * Constructor wrapper
     *
     * @param  string  $name
     * @param  string  $singular
     * @param  string  $plural
     *
     * @return CreateCustomPostType
     */
    public static function called(string $name, string $singular = '', string $plural = ''): CreateCustomPostType
    {
        return new static($name, $singular, $plural);
    }



    /**
     * Add support
     *
     * @param  string|array  $for
     *
     * @return $this
     */
    public function addSupportFor($for): CreateCustomPostType
    {
        // Turn it into an array if it's not one
        $for = is_array($for) ? $for : [$for];

        $supports = array_merge($for, $this->definition['supports']);
        $this->set('supports', $supports);

        return $this;
    }



    /**
     * Add a taxonomy
     *
     * @param  string  $taxonomy
     *
     * @return $this
     */
    public function addTaxonomy(string $taxonomy): CreateCustomPostType
    {
        $this->definition['taxonomies'][] = $taxonomy;

        return $this;
    }



    /**
     * Make sure this post is only accessible for administrators
     *
     * @return $this
     */
    public function adminOnly(): CreateCustomPostType
    {
        $this->set('capabilities', [
            'edit_post'          => 'update_core',
            'read_post'          => 'update_core',
            'delete_post'        => 'update_core',
            'edit_posts'         => 'update_core',
            'edit_others_posts'  => 'update_core',
            'delete_posts'       => 'update_core',
            'publish_posts'      => 'update_core',
            'read_private_posts' => 'update_core',
        ]);

        return $this;
    }



    /**
     * Set up a custom post type to work only in the backend.
     *
     * @return $this
     */
    public function backendOnly(): CreateCustomPostType
    {
        $this->set('rewrite', false);
        $this->set('query_var', false);
        $this->set('publicly_queryable', false);
        $this->set('public', false);

        return $this;
    }



    /**
     * Set up a custom post type to work in the front-end
     *
     * @return $this
     */
    public function frontend(): CreateCustomPostType
    {
        $this->set('rewrite', true);
        $this->set('query_var', true);
        $this->set('publicly_queryable', true);
        $this->set('public', true);

        return $this;
    }



    /**
     * Set up a custom post type to be hidden.
     *
     * @return $this
     */
    public function hidden(): CreateCustomPostType
    {
        $this->set('show_ui', false);
        $this->set('show_in_nav_menus', false);

        return $this;
    }



    public function icon(string $icon): CreateCustomPostType
    {
        $this->set('menu_icon', $icon);

        return $this;
    }



    /**
     * Register this custom post type
     *
     * @param  int  $sequence
     */
    public function register(int $sequence = 20): void
    {
        HookInto::action('init', $sequence)
            ->run(function () {
                register_post_type($this->postType, $this->definition);
            });
    }



    /**
     * Remove support for
     *
     * @param $for
     *
     * @return $this
     */
    public function removeSupportFor($for): CreateCustomPostType
    {
        // Turn it into an array if it's not one
        $for = is_array($for) ? $for : [$for];

        foreach ($for as $support) {
            if (($key = array_search($support, $this->definition['supports'], true)) !== false) {
                unset($this->definition['supports'][$key]);
            }
        }

        return $this;
    }



    /**
     * set any attribute
     *
     * @param $attribute
     * @param $value
     *
     * @return $this
     */
    public function set($attribute, $value): CreateCustomPostType
    {
        $this->definition[$attribute] = $value;

        return $this;
    }



    /**
     * set a label
     *
     * @param $label
     * @param $name
     *
     * @return $this
     */
    public function setLabel($label, $name): CreateCustomPostType
    {
        $this->definition['labels'][$label] = $name;

        return $this;
    }



    /**
     * set labels
     *
     * @param  array  $labels
     *
     * @return $this
     */
    public function setLabels(array $labels): CreateCustomPostType
    {
        foreach ($labels as $label => $value) {
            $this->setLabel($label, $value);
        }

        return $this;
    }

}
