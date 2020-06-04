<?php declare(strict_types = 1);

namespace DIF\Services;

use DIF\Models\DuplicateFile;

interface DuplicatesRendererInterface
{
    public function render(bool $isPrioritizeMatch, DuplicateFile ...$files);
}