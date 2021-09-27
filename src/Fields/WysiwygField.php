<?php

namespace GeniePress\Fields;

class WysiwygField extends TextField
{

    /**
     * Hide the medias upload button?
     *
     * @param  bool  $mediaUpload
     *
     * @return $this
     */
    public function mediaUpload(bool $mediaUpload): WysiwygField
    {
        return $this->set('media_upload', $mediaUpload);
    }



    /**
     * Specify which tabs are available. Defaults to 'all'. Choices of 'all' (Visual & Text), 'visual' (Visual Only) or text (Text Only)
     *
     * @param  string  $tabs
     *
     * @return $this
     */
    public function tabs(string $tabs): WysiwygField
    {
        return $this->set('tabs', $tabs);
    }



    /**
     * Specify the editor's toolbar. Defaults to 'full'.
     * Choices of 'full' (Full), 'basic' (Basic) or a custom toolbar (https://www.advancedcustomfields.com/resources/customize-the-wysiwyg-toolbars/)
     *
     * @param  string  $toolbar  full|basic|custom
     *
     * @return $this
     */
    public function toolbar(string $toolbar): WysiwygField
    {
        return $this->set('toolbar', $toolbar);
    }



    /**
     * Set Defaults
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();
        $this->type('wysiwyg');
        $this->tabs('all');
        $this->toolbar('basic');
        $this->mediaUpload(false);
    }

}
