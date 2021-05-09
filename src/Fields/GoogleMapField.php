<?php

namespace GeniePress\Fields;

use GeniePress\Abstracts\Field;

class GoogleMapField extends Field
{

    /**
     * @param $latitude
     *
     * @return GoogleMapField
     */
    public function centerLatitude($latitude): GoogleMapField
    {
        return $this->set('center_lat', $latitude);
    }



    /**
     * @param $longitude
     *
     * @return GoogleMapField
     */
    public function centerLongitude($longitude): GoogleMapField
    {
        return $this->set('center_lng', $longitude);
    }



    /**
     * @param $zoom
     *
     * @return GoogleMapField
     */
    public function zoom($zoom): GoogleMapField
    {
        return $this->set('zoom', $zoom);
    }



    /**
     * Set defaults for this field
     */
    protected function setDefaults()
    {
        parent::setDefaults();
        $this->type('google_map');
    }

}
