<?php

namespace GeniePress\Casts;

use DateTime;
use DateTimeZone;
use Exception;
use GeniePress\Interfaces\Cast;

class DateCast extends DateTimeCast
{

    protected static $returnFormat = 'Ymd';

}
