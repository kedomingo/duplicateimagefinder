<?php

class ImageResourceFactory
{
    private const DEFAULT_SCALE_WIDTH = 32;

    /**
     * @param string ...$filenames
     *
     * @return ImageResource[]
     * @throws Exception
     */
    public function getImageResources(string ...$filenames) : array
    {
        $precheckedFiles = $this->findDuplicateFiles(...$filenames);
        $imageResources  = [];

        $uniqueResources = [];
        foreach ($precheckedFiles as $file) {
            if (!isset($uniqueResources[$file->getUniqueIdentifier()])) {
                // ONLY CREATE ONE RESOURCE FOR A UNIQUE HASH
                $uniqueResources[$file->getUniqueIdentifier()] = 1;

                try {
                    $image = ImageResource::createFromFile($file);
                } catch (UnsupportedImageException $e) {
                    continue;
                }
                $imageResource    = $image->scale(self::DEFAULT_SCALE_WIDTH); // Use a downscaled image as resource
                $imageResources[] = $imageResource;

                // LINK THE RESOURCE TO THE FILE and all its duplicates
                $file->setImageResource($imageResource);
            }
        }

        return $imageResources;
    }

    /**
     * Find exact duplicate files
     *
     * @param string ...$filenames
     *
     * @return FileInfo[]
     */
    private function findDuplicateFiles(string ...$filenames) : array
    {
        $fileInfos = [];
        // Group files by size
        foreach ($filenames as $filename) {
            $fileInfo                          = new FileInfo($filename);
            $fileInfos[$fileInfo->getSize()][] = $fileInfo;
        }
        $result = [];
        foreach ($fileInfos as $size => $possibleDuplicates) {
            $possibleduplicateCount = count($possibleDuplicates);
            if ($possibleduplicateCount === 1) {
                $result[] = $possibleDuplicates[0];
                continue;
            }
            $this->identifyActualDuplicatesFromSameSizeFiles($possibleDuplicates);
            $result = array_merge($result, $possibleDuplicates);
        }

        return $result;
    }

    /**
     * @param FileInfo[] $files
     */
    private function identifyActualDuplicatesFromSameSizeFiles(&$files)
    {
        $fileCount = count($files);
        for ($i = 0; $i < $fileCount; $i++) {
            for ($j = $i + 1; $j < $fileCount; $j++) {
                /**
                 * @var FileInfo[] $files
                 */
                if (empty($files[$i]->getTrueHash())) {
                    $files[$i]->setHash();
                }
                if (empty($files[$j]->getTrueHash())) {
                    $files[$j]->setHash();
                }
                if ($files[$i]->getTrueHash() === $files[$j]->getTrueHash()) {
                    $files[$i]->setDuplicateTo($files[$j]);
                    $files[$j]->setDuplicateTo($files[$i]);
                }
            }
        }
    }
}