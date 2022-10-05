<?php

declare(strict_types=1);

namespace Domain\Browser;

use Domain\Browser\DTOs\BrowserSetupData;

interface Browser
{
    public function setup(BrowserSetupData $browserSetupData): self;

    public function execute(): void;

    public function addTargets(array $targetDataArray): self;

    public static function make(): self;
}
