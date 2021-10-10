<?php

namespace GeniePress\Casts;

use GeniePress\Interfaces\Cast;

class Base64Cast implements Cast
{

    /**
     * After reading from the database, cast this value to
     *
     * @param  mixed  $value
     * @param  array  $field
     * @param  int  $post_id
     *
     * @return mixed
     */
    public static function get($value, array $field, int $post_id)
    {
        return unserialize(base64_decode($value), ['allowed_classes' => true]);
    }



    /**
     * Before saving to the database, cast this value from
     *
     * @param  mixed  $value
     * @param  array  $field
     * @param  int  $post_id
     *
     * @return object
     */
    public static function set($value, array $field, int $post_id): string
    {
        return base64_encode(serialize($value));
    }
}
