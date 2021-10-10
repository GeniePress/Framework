<?php

namespace GeniePress\Interfaces;

interface Cast
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
    public static function get($value, array $field, int $post_id);



    /**
     * Before saving to the database, cast this value from
     *
     * @param  mixed  $value
     * @param  array  $field
     * @param  int  $post_id
     *
     * @return mixed
     */
    public static function set($value, array $field, int $post_id);

}
