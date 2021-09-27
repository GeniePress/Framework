<?php

namespace GeniePress\Utilities;

use ArrayObject;

class Collection extends ArrayObject
{

    /**
     * Add an item to the collection.
     *
     * @param  mixed  $item
     *
     * @return $this
     */
    public function add($item): Collection
    {
        $this->append($item);

        return $this;
    }



    /**
     * Return all the items
     *
     * @return array
     */
    public function all(): array
    {
        return (array) $this;
    }



    /**
     * get the first item
     *
     * @return mixed
     */
    public function first()
    {
        if ($this->offsetExists(0)) {
            return $this->offsetGet(0);
        }

        return false;
    }



    /**
     * Determine if the collection is empty or not.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }



    /**
     * Convert this to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->getArrayCopy();
    }

}
