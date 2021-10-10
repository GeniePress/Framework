<?php

namespace GeniePress\Casts;

use DateTime;
use DateTimeZone;
use GeniePress\Interfaces\Cast;

class TimeCast implements Cast
{

    /**
     * After reading from the database, cast this value to
     *
     * @param  mixed  $value
     * @param  array  $field
     * @param  int  $post_id
     *
     * @return DateTime
     */
    public static function get($value, array $field, int $post_id): DateTime
    {
        $tz = new DateTimeZone(wp_timezone_string());

        return DateTime::createFromFormat('H:i:s', $value, $tz);
    }



    /**
     * Before saving to the database, cast this value from
     *
     * @param  DateTime  $value
     * @param  array  $field
     * @param  int  $post_id
     *
     * @return object
     */
    public static function set($value, array $field, int $post_id): string
    {
        return $value->format('H:i:s');
    }
}
