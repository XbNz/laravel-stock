<?php

namespace App\Filament\Resources\TrackingRequestResource\Pages;

use App\Filament\Resources\TrackingRequestResource;
use Carbon\CarbonInterval;
use Closure;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\Rules\ReasonableUpdateIntervalRule;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Actions;
use Filament\Resources\Form;
use Filament\Resources\Pages\EditRecord;

class EditTrackingRequest extends EditRecord
{
    protected static string $resource = TrackingRequestResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
        ];
    }

    protected function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
                TextInput::make('update_interval')
                    ->formatStateUsing(function (string $state) {
                        return CarbonInterval::seconds($state)->cascade()->forHumans(short: true);
                    })
                    ->label('Update interval')
                    ->rules([
                        new ReasonableUpdateIntervalRule()
                    ])
            ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['update_interval'] = CarbonInterval::fromString($data['update_interval'])->totalSeconds;
        return $data;
    }
}
