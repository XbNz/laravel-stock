<?php

declare(strict_types=1);

namespace App\Console\Stores\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class RefreshGluetunServerCommand extends Command
{
    protected $signature = 'refresh:gluetun';

    protected $description = 'Command description';

    public function handle(): int
    {
        Http::put('http://127.0.0.1:8002/v1/openvpn/status', [
            'status' => 'stopped',
        ]);

        Http::put('http://127.0.0.1:8002/v1/openvpn/status', [
            'status' => 'running',
        ]);

        return 0;
    }
}
