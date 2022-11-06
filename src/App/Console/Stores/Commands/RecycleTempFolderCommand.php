<?php

declare(strict_types=1);

namespace App\Console\Stores\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RecycleTempFolderCommand extends Command
{
    protected $signature = 'recycle:temp-folder';

    protected $description = 'Command description';

    public function handle(): int
    {
        $this->info('Recycling temp folder');

        if (File::exists(storage_path('app/tmp'))) {
            File::deleteDirectory(storage_path('app/tmp'));
            File::makeDirectory(storage_path('app/tmp'));
        }

        return 0;
    }
}
