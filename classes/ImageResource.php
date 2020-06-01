<?php

class ImageResource
{
    private const SUPPORTED_MIMES = [
        IMAGETYPE_JPEG,
        IMAGETYPE_PNG,
        IMAGETYPE_GIF,
        IMAGETYPE_BMP,
    ];

    private $img;

    /**
     * ImageResource constructor.
     */
    private function __construct()
    {
    }

    /**
     * @param $img
     * @return ImageResource
     */
    public static function createFromResource($img)
    {
        $instance = new ImageResource();
        $instance->setResource($img);

        return $instance;
    }

    /**
     * @param string $filename
     * @return ImageResource
     * @throws Exception
     */
    public static function createFromFilename(string $filename)
    {
        $mimetype = exif_imagetype($filename);
        if (!in_array($mimetype, self::SUPPORTED_MIMES)) {
            throw new UnsupportedImageException(sprintf("Unsupported mimetype for %s: %s", $filename, $mimetype));
        }

        $img = null;
        switch ($mimetype) {
            case IMAGETYPE_JPEG:
                $img = imagecreatefromjpeg($filename);
                break;

            case IMAGETYPE_PNG:
                $img = imagecreatefrompng($filename);
                break;

            case IMAGETYPE_GIF:
                $img = imagecreatefromgif($filename);
                break;

            case IMAGETYPE_BMP:
                $img = imagecreatefrombmp($filename);
                break;
        }
        $instance = new ImageResource();
        $instance->setResource($img);

        return $instance;
    }

    /**
     * @param $img
     */
    public function setResource($img)
    {
        $this->img = $img;
    }

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->img;
    }

    /**
     * @return false|int
     */
    public function getWidth()
    {
        return imagesx($this->img);
    }

    /**
     * @return false|int
     */
    public function getHeight()
    {
        return imagesy($this->img);
    }

    public function getColorAt($x, $y) : ImageColor
    {
        return new ImageColor($this->img, imagecolorat($this->img, $x, $y));
    }

}