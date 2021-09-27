<?php

namespace GeniePress\Fields;

use GeniePress\Abstracts\Field;

class TabField extends Field
{

    /**
     * Is this tab an Endpoint?
     *
     * @param  bool  $endpoint
     *
     * @return $this
     */
    public function endpoint(bool $endpoint): TabField
    {
        return $this->set('endpoint', $endpoint);
    }



    /**
     * Tab Placement
     *
     * @param  string  $placement
     *
     * @return $this
     */
    public function placement(string $placement): TabField
    {
        return $this->set('placement', $placement);
    }



    /**
     * Set Defaults
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();
        $this->type('tab');
        $this->displayOnly(true);
        $this->placement('top');
    }

}
