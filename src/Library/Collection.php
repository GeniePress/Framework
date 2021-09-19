<?php

namespace GeniePress\Library;

use ArrayAccess;
use ArrayIterator;

class Collection implements ArrayAccess
{

    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $items = [];



    /**
     * Create a new collection.
     *
     * @param  mixed  $items
     *
     * @return void
     */
    public function __construct($items = [])
    {
        $this->items = $this->$items;
    }



    /**
     * Get all of the items in the collection.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }



    /**
     * @return mixed
     */
    public function first()
    {
        return $this->items[0];
    }



    /**
     * Determine if the collection is empty or not.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items);
    }



    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }



    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }



    /**
     * Add an item to the collection.
     *
     * @param  mixed  $item
     *
     * @return $this
     */
    public function add($item): Collection
    {
        $this->items[] = $item;

        return $this;
    }



    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed  $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->items);
    }



    /**
     * Get an item at a given offset.
     *
     * @param  mixed  $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
    }



    /**
     * Set the item at a given offset.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }



    /**
     * Unset the item at a given offset.
     *
     * @param  string  $key
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->items[$key]);
    }
}
