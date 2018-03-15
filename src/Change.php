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
     * @param string $path
     * @param string $message
     * @param mixed $originalValue
     * @param mixed $newValue
     */
    public function __construct($path, $message, $originalValue = null, $newValue = null)
    {
        $this->path = $path;
        $this->message = $message;
        $this->originalValue = $originalValue;
        $this->newValue = $newValue;
    }

}