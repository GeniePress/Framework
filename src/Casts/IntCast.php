<?php

namespace GeniePress\Casts;

use GeniePress\Interfaces\Cast;

class IntCast implements Cast
{

    /**
     * After reading from the database, cast this value to
     *
     * @param  mixed  $value
     * @param  array  $field
     * @param  int  $post_id
     *
     * @return int
     */
    public static function get($value, array $field, int $post_id): int
    {
        return (int) $value;
    }



    /**
     * Before saving to the database, cast this value from
     *
     * @param  mixed  $value
     * @param  array  $field
     * @param  int  $post_id
     *
     * @return int
     */
    public static function set($value, array $field, int $post_id): int
    {
        return (int) $value;
    }
}
