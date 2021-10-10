<?php

namespace GeniePress\Abstracts;

use GeniePress\Cache;
use GeniePress\Debug;
use GeniePress\Genie;
use GeniePress\Interfaces\GenieComponent;
use GeniePress\Registry;
use GeniePress\Traits\HasData;
use GeniePress\Utilities\Collection;
use GeniePress\Utilities\ConvertString;
use GeniePress\Utilities\HookInto;
use GeniePress\WordPress;
use JsonSerializable;
use WP_Error;

/**
 * Class CustomPost
 * Abstract class for all Custom post types.
 *
 * @property int ID
 * @property string post_author
 * @property string post_date
 * @property string post_date_gmt
 * @property string post_content
 * @property string post_title
 * @property string post_excerpt
 * @property string post_status
 * @property string comment_status
 * @property string ping_status
 * @property string post_password
 * @property string post_name
 * @property string to_ping
 * @property string pinged
 * @property string post_modified
 * @property string post_modified_gmt
 * @property string post_content_filtered
 * @property int post_parent
 * @property string guid
 * @property int menu_order
 * @property string post_type
 * @property string post_mime_type
 * @property string comment_count
 */
abstract class CustomPost implements JsonSerializable, GenieComponent
{

    use HasData;

    /**
     * WordPress Post Type
     *
     * @var string
     */
    public static $postType = 'post';

    /**
     * Should this be cached ?
     *
     * @var bool
     */
    protected static $cache = false;

    /**
     * Singular version of the post_type
     *
     * @var
     */
    protected static $singular;

    /**
     * Plural version of the post_type
     *
     * @var
     */
    protected static $plural;

    /**
     *  Use the gutenberg editor ?
     *
     * @var bool
     */
    protected static $useGutenberg = false;

    /**
     * Do we want to autoTrigger a save after the post has been saved in the WordPress admin?
     * @var bool
     */
    protected static $triggerSave = true;

    /**
     * used as a store of loaded data
     *
     * @var array
     */
    protected $originalData = [];



    /**
     * Setup WordPress Hooks, filters and register necessary method calls.
     */
    public static function setup()
    {
        HookInto::action('acf/save_post', 20)
            ->run(function ($post_id) {
                global $post;

                if ( ! $post || $post->post_type !== static::$postType) {
                    return;
                }

                // Trigger a save
                if (static::$triggerSave) {
                    static::getById($post_id)->save();
                } elseif (static::$cache) {
                    Cache::clearPostCache($post_id);
                }
            });

        // After the post is saved... allow some of WordPress fields to be overWritten
        HookInto::filter('wp_insert_post_data')
            ->run(function ($data, $postArray) {
                // Make sure we have acf data
                if (empty($postArray['acf'])) {
                    return $data;
                }

                // Not this post type?
                $postType = $data['post_type'];
                if ($postType !== static::$postType) {
                    return $data;
                }

                $fields = static::getFields();

                foreach ($fields as $field) {
                    if ($field['override']) {
                        $value = $postArray['acf'][$field['key']];
                        $field = $field['override'];
                        if (is_callable($field)) {
                            [$field, $value] = $field($value);
                        }
                        if (in_array($field, WordPress::$postFields, true)) {
                            $data[$field] = $value;
                        }
                    }
                }

                return static::override($data, $postArray);
            });

        // Should we use Gutenberg ?
        HookInto::filter('use_block_editor_for_post_type')
            ->run(function ($currentStatus, $postType) {
                if ($postType === static::$postType) {
                    return static::$useGutenberg;
                }

                return $currentStatus;
            });
    }



    /**
     * Return a new instance of the Object
     *
     * @param  int|mixed  $id
     *
     */
    public function __construct($id = null)
    {
        // new custom post ?
        if ( ! $id) {
            $this->setDefaults();

            return;
        }

        // try and load this object from cache
        if (static::$cache) {
            $data = get_post_meta($id, static::getCacheKey(), true);
            if ( ! empty($data)) {
                $this->data = $data;
            }
        }

        // nothing from cache ?
        if (empty($this->data)) {
            $postData = get_post($id, ARRAY_A);

            // No data ?
            if ( ! $postData) {
                return;
            }

            // This causes WordPress to load and cache call metadata so
            // subsequent calls to get_field don't hit the db
            get_post_meta($id);

            // load our fields definitions
            $fields = static::getFields();

            foreach ($fields as $field) {
                if ($field['displayOnly']) {
                    continue;
                }

                $name  = $field['name'];
                $value = get_field($name, $id);

                if (isset($field['cast']) && method_exists($field['cast'], 'get')) {
                    $value = $field['cast']::get(get_field($name, $id, false), $field, $id);
                }

                $postData[$name] = $value;
            }

            $this->fill($postData);

            //Cache?
            if (static::$cache) {
                $this->beforeCache();
                update_post_meta($this->ID, static::getCacheKey(), $this->data);
            }
        }

        // clone - just in case we have objects
        $this->originalData = self::clone($this->data);

        $this->afterRead();
    }



    /**
     * Create an Object from an array of key value pairs.
     *
     * @param  array  $array
     *
     * @return static
     */
    public static function create(array $array = []): CustomPost
    {
        $object = new static();
        $object->setDefaults();
        $object->fill($array);
        $object->save();

        return $object;
    }



    /**
     * Wrapper around get_posts. Returns an array of Objects
     *
     * @param  array  $params
     *
     * @return Collection|static[]
     */
    public static function get(array $params = []): Collection
    {
        $defaultArgs = [
            'numberposts' => -1,
            'orderby'     => 'date',
            'order'       => 'DESC',
            'post_type'   => static::$postType,
            'post_status' => 'publish',
            'fields'      => 'ids',
        ];

        $defaultArgs = apply_filters(Genie::hookName(static::$postType.'_get_args'), $defaultArgs);
        $posts       = get_posts(array_merge($defaultArgs, $params));
        $collection  = new Collection();
        foreach ($posts as $id) {
            $collection->append(new static($id));
        }

        return $collection;
    }



    /**
     * Return a new instance of this class
     *
     * @param $id
     *
     * @return static
     */
    public static function getById($id): CustomPost
    {
        return new static($id);
    }



    /**
     * Find a post by its slug
     *
     * @param $slug
     *
     * @return bool|static
     */
    public static function getBySlug($slug)
    {
        $objects = static::get([
            'name'        => $slug,
            'post_status' => 'any',
            'fields'      => 'ids',
        ]);

        if ($objects->isEmpty()) {
            return false;
        }

        return $objects->first();
    }



    /**
     * Get All posts based on a Taxonomy Name
     *
     * @param $name
     * @param $taxonomy
     *
     * @return Collection|static[]
     */
    public static function getByTaxonomyName($name, $taxonomy): Collection
    {
        $term = get_term_by('name', $name, $taxonomy);

        return static::get([
            'tax_query' => [
                [
                    'taxonomy' => $taxonomy,
                    'field'    => 'term_id',
                    'terms'    => [$term->term_id],
                ],
            ],
        ]);
    }



    /**
     * Find a post by its title
     *
     * @param $title
     *
     * @return bool|static
     */
    public static function getByTitle($title)
    {
        $objects = static::get([
            'title' => $title,
        ]);

        if ($objects->isEmpty()) {
            return false;
        }

        return $objects->first();
    }



    /**
     * Useful for Templates
     *
     * @return null|static
     */
    public static function getCurrent(): ?CustomPost
    {
        return new static(get_the_ID());
    }



    /**
     * Get the field definitions from the registry
     *
     * @return mixed|null
     */
    public static function getFields()
    {
        return Registry::get('fields', static::class);
    }



    /**
     * Look through this custom post's schema and return the key for a field.
     * This allows us to use the key when creating new data with update_field.
     * The schema has to be attached from CreateSchema
     *
     * @param  string  $name
     *
     * @return mixed
     */
    public static function getKey(string $name)
    {
        $schema = Registry::get('schemas', static::class);

        return static::findKey($name, $schema['fields']);
    }



    /**
     * get the Plural name of the post type
     *
     * @return string
     */
    public static function getPlural(): string
    {
        return static::$plural ?? ConvertString::from(static::$postType)->toPlural()->toTitleCase()->return();
    }



    /**
     * Get the full Schema Definition
     *
     * @return mixed|null
     */
    public static function getSchema()
    {
        return Registry::get('schemas', static::class);
    }



    /**
     * get the Singular of the post type
     *
     * @return string
     */
    public static function getSingular(): string
    {
        return static::$singular ?? ConvertString::from(static::$postType)->toSingular()->toTitleCase()->return();
    }



    /**
     * clear the cache for this post
     */
    public function clearCache(): void
    {
        if ($this->ID && static::$cache) {
            Cache::clearPostCache($this->ID);
        }
    }



    /**
     * Delete this post
     *
     * @param  bool  $force
     *
     * @return bool
     */
    public function delete(bool $force = true): bool
    {
        if ($this->ID) {
            $this->beforeDelete();

            return wp_delete_post($this->ID, $force);
        }

        return false;
    }



    /**
     * Return an array of images sizes and urls.
     *
     * @param  string  $size
     *
     * @return array|object|false
     */
    public function featuredImage(string $size = '')
    {
        // Does this post have a featured image?
        $attachmentID = get_post_thumbnail_id($this->ID);

        if ( ! $attachmentID) {
            return false;
        }
        $images = [];

        $sizes = get_intermediate_image_sizes();
        foreach ($sizes as $imageSize) {
            $src           = wp_get_attachment_image_src($attachmentID, $imageSize);
            $images[$size] = (object) [
                'url'     => $src[0],
                'width'   => $src[1],
                'height'  => $src[2],
                'resized' => $src[3],
            ];
            if ($size && $imageSize === $size) {
                return $images[$size];
            }
        }

        return $images;
    }



    /**
     * Check if the post needs saving
     *
     * @return bool
     */
    public function isDirty(): bool
    {
        return $this->data !== $this->originalData;
    }



    /**
     * What should we serialize when json_encode is called on the object
     *
     * @return mixed|void
     */
    public function jsonSerialize()
    {
        return $this->data;
    }



    /**
     * get the permalink for this post
     *
     * @return false|string|WP_Error
     */
    public function permalink()
    {
        return get_permalink($this->ID);
    }



    /**
     * Save the custom post type
     *
     * @return $this
     */
    public function save(): CustomPost
    {
        $this->beforeSave();

        $this->checkValidity();

        if ( ! $this->isDirty()) {
            return $this;
        }

        $postFields = [];
        foreach (WordPress::$postFields as $field) {
            if ($this->$field) {
                $postFields[$field] = $this->$field;
            }
        }

        $this->ID = wp_insert_post($postFields);

        $fields = static::getFields();

        if ($fields) {
            foreach ($fields as $field) {
                if ($field['displayOnly']) {
                    continue;
                }

                $name = $field['name'];
                $key  = $field['key'];

                if ( ! array_key_exists($name, $this->data)) {
                    continue;
                }

                // Only update if we need to.
                if ( ! array_key_exists($name, $this->originalData) || $this->originalData[$name] !== $this->data[$name]) {
                    $value = $this->data[$name];
                    if (isset($field['cast']) && method_exists($field['cast'], 'set')) {
                        $value = $field['cast']::set($value, $field, $this->ID);
                    }

                    update_field($key, $value, $this->ID);
                }
            }
        }

        // clone - just in case we have objects
        $this->originalData = self::clone($this->data);

        $this->clearCache();

        $this->afterSave();

        return $this;
    }



    /**
     * After the post has been loaded from the database
     */
    protected function afterRead(): void
    {
    }



    /**
     * After save - Do something!
     */
    protected function afterSave(): void
    {
    }



    /**
     *  Update properties on this object
     */
    protected function beforeCache(): void
    {
    }



    /**
     * Things to do before delete!
     * Delete other objects / images etc.
     */
    protected function beforeDelete(): void
    {
    }



    /**
     * Before save - Set defaults / fill values
     */
    protected function beforeSave(): void
    {
    }



    /**
     * Check the validity of this object
     * Throw errors from here and catch from save
     */
    protected function checkValidity(): void
    {
    }



    /**
     * Set defaults for this object
     */
    protected function setDefaults(): void
    {
        $this->post_status = 'publish';
        $this->post_type   = static::$postType;
    }



    /**
     * Clone the properties of this object
     *
     * @param $array
     *
     * @return array
     */
    protected static function clone($array): array
    {
        return array_map(static function ($element) {
            if (is_array($element)) {
                return self::clone($element);
            }

            if (is_object($element)) {
                return clone $element;
            }

            return $element;
        }, $array);
    }



    /**
     * Recursive function to parse fields map looking for $name
     *
     * @param $name
     * @param $fields
     *
     * @return mixed
     */
    protected static function findKey($name, $fields)
    {
        foreach ($fields as $field) {
            if ($field['name'] === $name) {
                return $field['key'];
            }
            if (isset($field['sub_fields'])) {
                $found = static::findKey($name, $field['sub_fields']);
                if ($found) {
                    return $found;
                }
            }
        }

        return false;
    }



    /**
     * Cache key used for this post_type
     *
     * @return string
     */
    protected static function getCacheKey(): string
    {
        return Cache::getCachePrefix().'_object';
    }



    /**
     * Capture and use ACF before the post is saved.
     * We can override WordPress fields here.
     *
     * @param  array  $data
     * @param  array  $postArray
     *
     * @return array
     * @noinspection PhpUnusedParameterInspection
     */
    protected static function override(array $data, array $postArray): array
    {
        return $data;
    }

}
