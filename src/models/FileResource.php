<?php declare(strict_types = 1);

namespace DIF\Models;

/**
 * Class FileResource
 *
 * A composition of file information, duplicates information, and image resource
 */
final class FileResource
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $hash;

    /**
     * @var FileResource[]
     */
    private $duplicates;

    /**
     * @var ImageResource $resource
     */
    private $imageResource;

    /**
     * FileInfo constructor.
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        $this->name = $filename;
        $this->size = filesize($filename);
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getSize() : int
    {
        return $this->size;
    }

    /**
     * @return string|null
     */
    public function getUniqueIdentifier() : ?string
    {
        return $this->hash ?? md5($this->getName());
    }

    public function getTrueHash() : ?string
    {
        return $this->hash;
    }

    /**
     * Only do this when necessary
     *
     * @return void
     */
    public function setHash() : void
    {
        $this->hash = md5_file($this->getName());
    }

    /**
     * @param FileResource $duplicate
     */
    public function setDuplicateTo(FileResource $duplicate) : void
    {
        $this->duplicates[] = $duplicate;
    }

    /**
     * @return FileResource[]
     */
    public function getDuplicates() : array
    {
        return $this->duplicates ?? [];
    }

    /**
     * Recursively links the image resource to this file and its duplicates
     *
     * @param ImageResource $imageResource
     */
    public function setImageResource(ImageResource $imageResource) : void
    {
        if (!empty($this->imageResource)) {
            return;
        }
        $this->imageResource = $imageResource;
        foreach ($this->getDuplicates() as $fileInfo) {
            $fileInfo->setImageResource($imageResource);
        }
    }

    /**
     * @return ImageResource
     */
    public function getImageResource() : ImageResource
    {
        return $this->imageResource;
    }

    /**
     * @return ImageColor
     */
    public function getTotalColorAverage() : ImageColor
    {
        return $this->getImageResource()->getTotalColorAverage();
    }
}
