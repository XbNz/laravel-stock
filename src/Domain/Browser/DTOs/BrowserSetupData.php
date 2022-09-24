<?php

namespace Domain\Browser\DTOs;

use Domain\Browser\Browser;
use Webmozart\Assert\Assert;

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
