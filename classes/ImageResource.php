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
     * @var FileInfo
     */
    private $fileinfo;

    /**
     * @var ImageColor
     */
    private $totalColorAverage;

    /**
     * @var ImageResizer
     */
    private $resizer;

    /**
     * ImageResource constructor.
     *
     * @param ImageResizer $resizer
     */
    private function __construct(ImageResizer $resizer)
    {
        $this->resizer = $resizer;
    }

    /**
     * Create a resource that is a placeholder for a gd resource
     *
     * @param $img
     * @return ImageResource
     */
    public static function createFromResource($img)
    {
        $instance = new ImageResource(new ImageResizer());
        $instance->setResource($img);

        return $instance;
    }

    /**
     * Create a resource from a given file
     *
     * @param FileInfo $fileInfo
     *
     * @return ImageResource
     * @throws UnsupportedImageException
     */
    public static function createFromFile(FileInfo $fileInfo)
    {
        $filename = $fileInfo->getName();
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
        $instance = new ImageResource(new ImageResizer());
        $instance->setFileInfo($fileInfo);
        $instance->setResource($img);
        $instance->setTotalColorAverage($instance->calculateTotalAverage());

        return $instance;
    }

    /**
     * @param FileInfo $fileInfo
     * @return void
     */
    private function setFileInfo(FileInfo $fileInfo) : void
    {
        $this->fileinfo = $fileInfo;
    }

    /**
     * @return bool
     */
    public function isDuplicateFile() : bool
    {
        return $this->fileinfo->hasDuplicates();
    }

    /**
     * Accessor to file hash
     *
     * @return string
     */
    public function getFileIdentifier() : string
    {
        return $this->fileinfo->getUniqueIdentifier();
    }

    /**
     * Accessor to filename
     *
     * @return string
     */
    public function getFilename() : string
    {
        return $this->fileinfo->getName();
    }

    /**
     * @param int  $width
     * @param null $height
     * @param bool $clone
     *
     * @return ImageResource
     */
    public function scale(int $width, $height = null, $clone = false)
    {
        $resource = $this->resizer->scale($this, $width, $height);
        if ($clone) {
            return static::createFromResource($resource);
        }
        $this->setResource($resource);

        return $this;
    }

    /**
     * Calculate the average scene color of the image
     *
     * @return ImageColor
     */
    private function calculateTotalAverage() : ImageColor
    {
        $resource = $this->scale(1, 1, true);

        return $resource->getColorAt(0, 0);
    }

    /**
     * @param ImageColor $color
     */
    public function setTotalColorAverage(ImageColor $color) : void
    {
        $this->totalColorAverage = $color;
    }

    /**
     * @return ImageColor
     */
    public function getTotalColorAverage() : ImageColor
    {
        return $this->totalColorAverage;
    }

    /**
     * @param ImageResource $other
     * @return int|lt
     */
    public function compareColorAverageTo(ImageResource $other) : int
    {
        return strcmp($this->getTotalColorAverage()->getRGBSequence(),
            $other->getTotalColorAverage()->getRGBSequence());
    }

    /**
     * @param $img
     */
    private function setResource($img)
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