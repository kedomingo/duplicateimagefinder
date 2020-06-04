<?php

class FileResourceFactory
{
    private const DEFAULT_SCALE_WIDTH = 32;

    /**
     * @param string ...$filenames
     *
     * @return FileResource[]
     * @throws Exception
     */
    public function getFileResources(string ...$filenames) : array
    {
        $fileInfos = [];
        foreach ($filenames as $filename) {
            $fileInfos[] = new FileResource($filename);
        }

        $precheckedFiles = $this->findDuplicateFiles(...$fileInfos);
        $imageResources  = [];

        $uniqueResources = [];
        foreach ($precheckedFiles as $k => $file) {
            if (!isset($uniqueResources[$file->getUniqueIdentifier()])) {
                // ONLY CREATE ONE RESOURCE FOR A UNIQUE HASH
                $uniqueResources[$file->getUniqueIdentifier()] = 1;

                try {
                    $image = ImageResource::createFromFile($file);
                } catch (UnsupportedImageException $e) {
                    unset($precheckedFiles[$k]);
                    continue;
                }
                $imageResource    = $image->scale(self::DEFAULT_SCALE_WIDTH); // Use a downscaled image as resource
                $imageResources[] = $imageResource;

                // LINK THE RESOURCE TO THE FILE and all its duplicates
                $file->setImageResource($imageResource);
            }
        }

        return $precheckedFiles;
    }

    /**
     * Find exact duplicate files
     *
     * @param FileResource[] $files
     * @return FileResource[]
     */
    private function findDuplicateFiles(FileResource ...$files) : array
    {
        $fileInfos = [];
        // Group files by size
        foreach ($files as $fileInfo) {
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
     * @param FileResource[] $files
     */
    private function identifyActualDuplicatesFromSameSizeFiles(&$files)
    {
        $fileCount = count($files);
        for ($i = 0; $i < $fileCount; $i++) {
            for ($j = $i + 1; $j < $fileCount; $j++) {
                /**
                 * @var FileResource[] $files
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