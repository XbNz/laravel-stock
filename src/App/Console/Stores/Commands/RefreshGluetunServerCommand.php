<?php

namespace App\Console\Stores\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

class RefreshGluetunServerCommand extends Command
{
    protected $signature = 'refresh:gluetun';

    protected $description = 'Command description';

    public function handle()
    {
        Http::put('http://127.0.0.1/v1/updater/status', [
            'status' => 'stopped',
        ]);

        Http::put('http://127.0.0.1/v1/updater/status', [
            'status' => 'running',
        ]);
    }
}
