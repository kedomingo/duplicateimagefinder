<?php

namespace DIF\Services;

use DIF\Models\DuplicateFile;

interface DuplicatesRendererInterface
{
    public function render(bool $isPrioritizeMatch, DuplicateFile ...$files);
}