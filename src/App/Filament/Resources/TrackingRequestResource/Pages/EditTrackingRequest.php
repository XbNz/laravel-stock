<?php

namespace App\Filament\Resources\TrackingRequestResource\Pages;

use App\Filament\Resources\TrackingRequestResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrackingRequest extends EditRecord
{
    protected static string $resource = TrackingRequestResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
