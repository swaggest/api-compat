<?php

namespace Swaggest\ApiCompat;


class Change
{
    public $path;
    public $message;
    public $originalValue;
    public $newValue;

    /**
     * Change constructor.
     * @param $path
     * @param $message
     * @param $originalValue
     * @param $newValue
     */
    public function __construct($path, $message, $originalValue = null, $newValue = null)
    {
        $this->path = $path;
        $this->message = $message;
        $this->originalValue = $originalValue;
        $this->newValue = $newValue;
    }

}