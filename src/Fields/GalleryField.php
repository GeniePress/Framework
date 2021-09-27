<?php

namespace GeniePress\Fields;

class GalleryField extends ImageField
{

    /**
     * Where to insert the image
     *
     * @param  string  $insert  append|prepend
     *
     * @return $this
     */
    public function insert(string $insert): GalleryField
    {
        return $this->set('insert', $insert);
    }



    /**
     * Specify the maximum posts allowed to be selected. Defaults to 0
     *
     * @param  int  $number
     *
     * @return $this
     */
    public function max(int $number): GalleryField
    {
        return $this->set('max', $number);
    }



    /**
     * Specify the minimum posts required to be selected. Defaults to 0
     *
     * @param  int  $number
     *
     * @return $this
     */
    public function min(int $number): GalleryField
    {
        return $this->set('min', $number);
    }



    /**
     * Set defaults for this field
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();
        $this->type('gallery');
        $this->insert('append');
    }

}
