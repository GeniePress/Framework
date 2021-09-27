<?php

namespace GeniePress\Interfaces;

interface GenieComponent
{

    /**
     * Every Genie component must have a setup method.
     *
     * @return mixed
     */
    public static function setup();

}
