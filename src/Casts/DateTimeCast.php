<?php

namespace GeniePress\Casts;

use DateTime;
use DateTimeZone;
use GeniePress\Debug;
use GeniePress\Interfaces\Cast;

class DateTimeCast implements Cast
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

        return DateTime::createFromFormat('Y-m-d H:i:s', $value, $tz);
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
        return $value->format('Y-m-d H:i:s');
    }
}
