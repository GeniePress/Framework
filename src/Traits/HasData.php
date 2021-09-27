<?php

namespace GeniePress\Traits;

trait HasData
{

    /**
     * Used to store the post data
     *
     * @var array
     */
    protected $data = [];



    /**
     * magic getter
     *
     * @param $var
     *
     * @return mixed
     */
    public function __get($var)
    {
        if (array_key_exists($var, $this->data)) {
            return $this->data[$var];
        }

        return false;
    }



    /**
     * Needed from twig templates
     *
     * @param $var
     *
     * @return bool
     */
    public function __isset($var)
    {
        return array_key_exists($var, $this->data);
    }



    /**
     * magic set
     *
     * @param $var
     * @param $value
     */
    public function __set($var, $value)
    {
        $this->data[$var] = $value;
    }



    /**
     * Fill data properties from an array
     *
     * @param  array  $array
     *
     * @return static
     */
    public function fill(array $array): self
    {
        foreach ($array as $field => $value) {
            $this->data[$field] = $value;
        }

        return $this;
    }



    /**
     * Return all data for this post
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

}
