<?php declare(strict_types = 1);

namespace DIF\Factory;

use DIF\Exception\UnsupportedImageException;
use DIF\Models\FileResource;
use DIF\Models\ImageResource;

final class ImageResourceFactory
{
    private const SUPPORTED_MIMES = [
        IMAGETYPE_JPEG,
        IMAGETYPE_PNG,
        IMAGETYPE_GIF,
        IMAGETYPE_BMP,
    ];

    /**
     * Create a resource from a given file
     *
     * @param FileResource $fileInfo
     *
     * @return ImageResource
     * @throws UnsupportedImageException
     */
    public static function createFromFile(FileResource $fileInfo) : ImageResource
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
        $instance = new ImageResource($img);

        return $instance;
    }

    /**
     * @param resource $img
     *
     * @return ImageResource
     */
    public static function createFromGDResource($img) : ImageResource
    {
        $instance = new ImageResource($img);

        return $instance;
    }
}