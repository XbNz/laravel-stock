<?php

namespace App\Filament\Resources\TrackingRequestResource\Pages;

use App\Filament\Resources\TrackingRequestResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrackingRequests extends ListRecords
{
    protected static string $resource = TrackingRequestResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
