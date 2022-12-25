<?php

namespace App\Filament\Resources\TrackingRequestResource\Pages;

use App\Filament\Resources\TrackingRequestResource;
use Carbon\CarbonInterval;
use Domain\TrackingRequests\Actions\CreateTrackingRequestAction;
use Domain\TrackingRequests\DTOs\CreateTrackingRequestData;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Database\Eloquent\Model;

class CreateTrackingRequest extends CreateRecord
{
    protected static string $resource = TrackingRequestResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $createAction = app(CreateTrackingRequestAction::class);

        $data['update_interval'] = CarbonInterval::fromString($data['update_interval'])->totalSeconds;

        $trackingRequestCreationData = new CreateTrackingRequestData(
            $data['name'],
            new Uri($data['url']),
            $data['update_interval'],
        );

        return $createAction($trackingRequestCreationData, auth()->user());
    }
}
