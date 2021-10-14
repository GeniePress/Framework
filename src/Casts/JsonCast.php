<?php

namespace GeniePress\Casts;

use GeniePress\Interfaces\Cast;
use JsonException;

class JsonCast implements Cast
{

    /**
     * After reading from the database, cast this value to
     *
     * @param  string  $value
     * @param  array  $field
     * @param  int  $post_id
     *
     * @return mixed
     */
    public static function get($value, array $field, int $post_id)
    {
        try {
            return json_decode($value, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return false;
        }
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
        try {
            return json_encode($value, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        } catch (JsonException $e) {
            return '';
        }
    }
}
