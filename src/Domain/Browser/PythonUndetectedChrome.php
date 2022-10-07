<?php

declare(strict_types=1);

namespace Domain\Browser;

use Domain\Browser\DTOs\BrowserSetupData;
use Domain\Browser\DTOs\TargetData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;

class PythonUndetectedChrome implements Browser
{
    private ?BrowserSetupData $browserSetupData = null;

    /** @var array<TargetData>|null */
    private ?array $targets = null;

    public function setup(BrowserSetupData $browserSetupData): self
    {
        $this->browserSetupData = $browserSetupData;
        return $this;
    }

    public function execute(): void
    {
        $json = $this->serializeToScriptCompatibleJson();

        $process = new Process([
            Config::get('browser.Domain\Browser\PythonUndetectedChrome.path_to_python_executable'),
            Config::get('browser.Domain\Browser\PythonUndetectedChrome.path_to_python_undetected_chrome'),
            $json,
        ]);

        $process->setTimeout(1000);
        $process->mustRun();
    }

    /**
     * @param array<TargetData> $targetDataArray
     */
    public function addTargets(array $targetDataArray): self
    {
        $this->targets = $targetDataArray;
        return $this;
    }

    public static function make(): self
    {
        return new self();
    }

    private function serializeToScriptCompatibleJson(): string
    {
        Assert::notNull($this->browserSetupData);
        Assert::notNull($this->targets);

        $targetDataArray = $this->targets;
        $browserSetupData = $this->browserSetupData;

        $targetsArray = Collection::make($targetDataArray)
            ->map(function (TargetData $targetData) {
                return Collection::make([
                    'screenshot_filename' => $targetData->screenShotFileName,
                    'html_output_filename' => $targetData->htmlFileName,
                    'url' => (string) $targetData->url,
                    'timeout' => $targetData->timeout->seconds,
                    'wait_for_xpath_element' => $targetData->xpathElementToWaitFor ?? null,
                ]);
            })->filter()->toArray();

        $wholeArray = [
            'targets' => $targetsArray,
            'selenium' => [
                'arguments' => $browserSetupData->arguments,
                'fullscreen' => $browserSetupData->fullPageScreenshot,
            ],
        ];

        return json_encode($wholeArray, JSON_THROW_ON_ERROR);
    }
}
