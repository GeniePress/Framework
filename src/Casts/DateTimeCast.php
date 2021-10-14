<?php

namespace GeniePress\Casts;

use DateTime;
use DateTimeZone;
use Exception;
use GeniePress\Interfaces\Cast;

class DateTimeCast implements Cast
{

    protected static $returnFormat = 'Y-m-d H:i:s';



    /**
     * After reading from the database, cast this value to
     *
     * @param  mixed  $value
     * @param  array  $field
     * @param  int  $post_id
     *
     * @return DateTime
     * @throws Exception
     */
    public static function get($value, array $field, int $post_id): DateTime
    {
        $tz = new DateTimeZone(wp_timezone_string());

        try {
            if ( ! $value) {
                $value = $field['default_value'];
            }
            if ($value) {
                return new DateTime($value, $tz);
            }
        } catch (Exception $e) {
        }

        return new DateTime('now', $tz);
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
        return $value->format(self::$returnFormat);
    }
}
