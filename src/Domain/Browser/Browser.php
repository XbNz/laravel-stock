<?php

namespace Domain\Browser;

use Domain\Browser\DTOs\BrowserSetupData;
use Domain\Browser\DTOs\TargetData;

interface Browser
{
    public function execute(): void;
    public function setup(BrowserSetupData $browserSetupData): self;
    public function addTargets(array $targetDataArray): self;
    public static function make(): self;
}
