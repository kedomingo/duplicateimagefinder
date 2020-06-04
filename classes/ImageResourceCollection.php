<?php

class ImageResourceCollection
{
    private $data;

    public function __construct(ImageResource ...$data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}