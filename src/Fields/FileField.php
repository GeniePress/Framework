<?php

namespace GeniePress\Fields;

class FileField extends TextField
{

    /**
     * Restrict the image library. Defaults to 'all'. Choices of 'all' (All Images) or 'uploadedTo' (Uploaded to post)
     *
     * @param  string  $library  all|uploadedTo
     *
     * @return $this
     */
    public function library(string $library): FileField
    {
        return $this->set('library', $library);
    }



    /**
     * Specify the maximum filesize in MB in px allowed when uploading. Defaults to 0.
     * The unit may also be included. e.g. "256KB"
     *
     * @param  string  $maxSize
     *
     * @return $this
     */
    public function maxSize(string $maxSize): FileField
    {
        return $this->set('max_size', $maxSize);
    }



    /**
     * Comma separated list of file type extensions allowed when uploading.
     * Defaults to ''
     *
     * @param  string  $mimeTypes
     *
     * @return $this
     */
    public function mimeTypes(string $mimeTypes): FileField
    {
        return $this->set('mime_types', $mimeTypes);
    }



    /**
     * Specify the minimum filesize in MB required when uploading. Defaults to 0.
     * The unit may also be included. e.g. '256KB'
     *
     * @param  string  $minSize
     *
     * @return $this
     */
    public function minSize(string $minSize): FileField
    {
        return $this->set('min_size', $minSize);
    }



    /**
     * Specify the image size shown when editing. Defaults to 'thumbnail'.
     *
     * @param $previewSize
     *
     * @return $this
     */
    public function previewSize($previewSize): FileField
    {
        return $this->set('preview_size', $previewSize);
    }



    /**
     * Specify the type of value returned by get_field(). Defaults to 'array'.
     * Choices of 'array' (Image Array), 'url' (Image URL) or 'id' (Image ID)
     *
     * @param $returnValue string
     *
     * @return $this
     */
    public function returnFormat(string $returnValue): FileField
    {
        return $this->set('return_format', $returnValue);
    }



    /**
     * Set Defaults
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();
        $this->type('file');
        $this->metaQuery('NUMERIC');
        $this->returnFormat('array');
        $this->previewSize('thumbnail');
        $this->library('all');
    }

}
