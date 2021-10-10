<?php

namespace GeniePress\Casts;

use GeniePress\Interfaces\Cast;

class StringCast implements Cast
{

    /**
     * After reading from the database, cast this value to
     *
     * @param  mixed  $value
     * @param  array  $field
     * @param  int  $post_id
     *
     * @return string
     */
    public static function get($value, array $field, int $post_id): string
    {
        return (string) $value;
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
        return (string) $value;
    }
}
