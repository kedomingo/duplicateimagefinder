<?php

namespace DIF\Controller;

use DIF\Exception\ContinuableException;
use DIF\Exception\InvalidArgumentException;
use DIF\Exception\MissingArgumentException;
use DIF\Models\DuplicateFile;
use DIF\Services\DuplicateImageFinderInterface;

class FinderController
{
    private const OPTION_FLAG_LONG        = 'long';
    private const OPTION_FLAG_SHORT       = 'short';
    private const OPTION_FLAG_IS_BOOLEAN  = 'isBoolean';
    private const OPTION_FLAG_IS_REQUIRED = 'isRequired';

    private const OPTION_DIRECTORY       = 'directory';
    private const OPTION_THRESHOLD       = 'threshold';
    private const OPTION_MATCH_PRIORITY  = 'matchpriority';
    private const OPTION_MOVE_DUPLICATES = 'moveduplicates';

    private const RUNTIME_OPTIONS = [
        self::OPTION_DIRECTORY       => [
            self::OPTION_FLAG_LONG        => 'dir',
            self::OPTION_FLAG_SHORT       => 'd',
            self::OPTION_FLAG_IS_REQUIRED => true,
            self::OPTION_FLAG_IS_BOOLEAN  => false
        ],
        self::OPTION_THRESHOLD       => [
            self::OPTION_FLAG_LONG        => 'threshold',
            self::OPTION_FLAG_SHORT       => 't',
            self::OPTION_FLAG_IS_REQUIRED => true,
            self::OPTION_FLAG_IS_BOOLEAN  => false
        ],
        self::OPTION_MATCH_PRIORITY  => [
            self::OPTION_FLAG_LONG        => 'match-priority',
            self::OPTION_FLAG_SHORT       => 'm',
            self::OPTION_FLAG_IS_REQUIRED => false,
            self::OPTION_FLAG_IS_BOOLEAN  => true
        ],
        self::OPTION_MOVE_DUPLICATES => [
            self::OPTION_FLAG_LONG        => 'move-duplicates',
            self::OPTION_FLAG_SHORT       => '',
            self::OPTION_FLAG_IS_REQUIRED => false,
            self::OPTION_FLAG_IS_BOOLEAN  => true
        ],
    ];

    /**
     * Default similarity threshold to be classified as simiar (in %)
     */
    private const DEFAULT_THRESHOLD = 60;
    /**
     * @var array
     */
    private $runtimeOptions;

    /**
     * @var string
     */
    private $inputDirectory;

    /**
     * @var int
     */
    private $threshold;

    /**
     * @var bool
     */
    private $isPrioritizeMatch;

    /**
     * @var bool
     */
    private $isMoveDuplicates;

    /**
     * @var DuplicateImageFinderInterface
     */
    private $duplicateImageFinder;

    /**
     * FinderController constructor.
     * @param DuplicateImageFinderInterface $duplicateImageFinder
     */
    public function __construct(DuplicateImageFinderInterface $duplicateImageFinder)
    {
        $this->duplicateImageFinder = $duplicateImageFinder;
        $this->runtimeOptions       = $this->getOptions();
        $this->inputDirectory       = $this->getArg($this->runtimeOptions, self::OPTION_DIRECTORY);
        $this->threshold            = $this->getArg($this->runtimeOptions, self::OPTION_THRESHOLD,
            self::DEFAULT_THRESHOLD);
        $this->isPrioritizeMatch    = $this->getArg($this->runtimeOptions, self::OPTION_MATCH_PRIORITY);
        $this->isMoveDuplicates     = $this->getArg($this->runtimeOptions, self::OPTION_MOVE_DUPLICATES);
    }

    public function start()
    {
        try {
            $this->checkArgs();
        } catch (ContinuableException $e) {
            echo $e->getMessage() . "\n";
            readline();
        } catch (MissingArgumentException|InvalidArgumentException $e) {
            echo $e->getMessage() . "\n";
            exit;
        }

        $result = $this->duplicateImageFinder->scan($this->inputDirectory, $this->threshold);

        $this->render(...$result);
    }

    /**
     * @param DuplicateFile ...$files
     */
    private function render(DuplicateFile ...$files) : void {
        echo "\n-------------------------\n";
        echo "Result\n";
        echo "-------------------------\n\n";
        foreach ($files as $filegroup) {
            $duplicates = $filegroup->getAlternates();
            if ($this->isPrioritizeMatch) {
                usort($duplicates, [$this, 'sortByScore']);
            }
            else {
                usort($duplicates, [$this, 'sortBySize']);
            }
            foreach ($duplicates as $k => $duplicate) {
                if ($k > 0) {
                    echo '    ';
                }
                echo ($duplicate->isBasisFile() ? '* ' : '') . $duplicate->getFilename()."\n";
            }
            echo "\n";
        }
    }

    /**
     * usorter
     *
     * @param DuplicateFile $file1
     * @param DuplicateFile $file2
     *
     * @return int
     */
    private function sortByScore(DuplicateFile $file1, DuplicateFile $file2) : int {
        $percentGroup1 = floor($file1->getScore() * 10);
        $percentGroup2 = floor($file2->getScore() * 10);
        if ($percentGroup1 != $percentGroup2) {
            return $percentGroup2 - $percentGroup1;
        }

        return $file2->getFilesize() - $file1->getFilesize();
    }

    /**
     * usorter
     *
     * @param DuplicateFile $file1
     * @param DuplicateFile $file2
     *
     * @return int
     */
    private function sortBySize(DuplicateFile $file1, DuplicateFile $file2) : int {
        return ($file2->getFilesize() * $file2->getScore()) - ($file1->getFilesize() * $file1->getScore());
    }

    /**
     * @throws MissingArgumentException
     * @throws InvalidArgumentException
     * @throws ContinuableException
     */
    private function checkArgs()
    {
        if (empty($this->inputDirectory) || empty($this->threshold)) {
            throw new MissingArgumentException('You must define the scan directory and duplicate threshold percent using -d and -t options');
        }
        if (!is_numeric($this->threshold) || $this->threshold < 1 || $this->threshold > 100) {
            throw new InvalidArgumentException('Threshold must be a number between 1 and 100');
        }
        if ($this->isPrioritizeMatch) {
            throw new ContinuableException('Priotitizing match percentage is faulty when you want to keep bigger files and there are duplicate smaller files. Use with caution. Press any key to continue. Ctrl+C to stop');
        }
    }


    /**
     * Parse the CLI options
     */
    private function getOptions()
    {
        $options = [];
        foreach (self::RUNTIME_OPTIONS as $option) {
            $flags = $option[self::OPTION_FLAG_IS_BOOLEAN] ? '' : ($option[self::OPTION_FLAG_IS_REQUIRED] ? ':' : '::');

            $options[$option[self::OPTION_FLAG_LONG] . $flags] = $option[self::OPTION_FLAG_SHORT] . $flags;
        }

        return getopt(implode($options), array_keys($options));
    }

    /**
     * Get the value of the parameter
     *
     * @param array  $args
     * @param string $argName
     * @param null   $default
     *
     * @return mixed|null
     */
    private function getArg(array $args, string $argName, $default = null)
    {
        $longOption      = self::RUNTIME_OPTIONS[$argName][self::OPTION_FLAG_LONG] ?? '';
        $shortOption     = self::RUNTIME_OPTIONS[$argName][self::OPTION_FLAG_SHORT] ?? '';
        $isBooleanOption = self::RUNTIME_OPTIONS[$argName][self::OPTION_FLAG_IS_BOOLEAN] ?? false;

        if ($isBooleanOption) {
            return isset($args[$longOption]) || isset($args[$shortOption]);
        }

        return isset($args[$longOption]) ? $args[$longOption] :
            (isset($args[$shortOption]) ? $args[$shortOption] : $default);
    }
}
