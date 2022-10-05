<?php

declare(strict_types=1);

namespace Domain\Browser\DTOs;

class BrowserSetupData
{
    /**
     * @param array<int, string> $arguments
     */
    public function __construct(
        public readonly array $arguments,
        public readonly bool $fullPageScreenshot = false,
    ) {
    }
}
