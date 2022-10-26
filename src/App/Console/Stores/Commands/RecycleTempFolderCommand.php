<?php

namespace App\Console\Stores\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RecycleTempFolderCommand extends Command
{
    protected $signature = 'recycle:temp-folder';

    protected $description = 'Command description';

    public function handle()
    {
        $this->info('Recycling temp folder');
        File::deleteDirectory(storage_path('app/temp'));
        File::makeDirectory(storage_path('app/temp'));
    }
}
