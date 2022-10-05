<?php

declare(strict_types=1);

namespace Domain\Browser\DTOs;

use Carbon\CarbonInterval;
use Psr\Http\Message\UriInterface;
use Webmozart\Assert\Assert;

class TargetData
{
    public function __construct(
        public readonly string $screenShotFileName,
        public readonly string $htmlFileName,
        public readonly UriInterface $url,
        public readonly CarbonInterval $timeout,
        public readonly ?string $xpathElementToWaitFor = null,
    ) {
        Assert::directory(dirname($this->screenShotFileName, 1));
        Assert::directory(dirname($this->htmlFileName, 1));
        Assert::nullOrStringNotEmpty($this->xpathElementToWaitFor);
    }
}
