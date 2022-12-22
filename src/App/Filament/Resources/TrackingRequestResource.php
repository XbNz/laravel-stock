<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrackingRequestResource\Pages;
use App\Filament\Resources\TrackingRequestResource\RelationManagers;
use Carbon\CarbonInterval;
use Domain\Stores\Enums\Store;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\States\TrackingRequestState;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\Column;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TrackingRequestResource extends Resource
{
    protected static ?string $model = TrackingRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->label('Name'),
                Tables\Columns\TextColumn::make('url')
                    ->searchable()
                    ->formatStateUsing(function (Column $column, string $state) {
                        if (blank($state)) {
                            return null;
                        }

                        return 'Link';
                    })
                    ->label('URL')
                    ->icon('heroicon-o-link')
                    ->url(fn (TrackingRequest $record) => $record->url)
                    ->openUrlInNewTab(true),
                Tables\Columns\TextColumn::make('store')
                    ->label('Store')
                    ->icon('heroicon-o-shopping-cart')
                    ->formatStateUsing(function (Column $column, string $state) {
                        if (blank($state)) {
                            return null;
                        }

                        return Store::from($state)->friendlyName();
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->icon('heroicon-o-eye')
                    ->sortable()
                    ->formatStateUsing(function (Column $column, string $state, TrackingRequest $record) {
                        if (blank($state)) {
                            return null;
                        }

                        return $record->status->friendlyName();
                    }),
                Tables\Columns\TextColumn::make('tracking_type')
                    ->label('Type')
                    ->icon('heroicon-o-document-text')
                    ->formatStateUsing(function (Column $column, string $state, TrackingRequest $record) {
                        if (blank($state)) {
                            return null;
                        }

                        return $record->tracking_type->friendlyName();
                    }),
                Tables\Columns\TextColumn::make('tracking_alerts_count')
                    ->label('Alerts')
                    ->sortable()
                    ->icon('heroicon-o-bell'),
                Tables\Columns\TextColumn::make('stocks_count')
                    ->label('Stocks')
                    ->sortable()
                    ->icon('heroicon-o-collection'),
                Tables\Columns\TextColumn::make('update_interval')
                    ->label('Updates every')
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->formatStateUsing(function (Column $column, string $state) {
                        if (blank($state)) {
                            return null;
                        }

                        return CarbonInterval::seconds($state)->cascade()->forHumans(short: true);
                    }),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last updated')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrackingRequests::route('/'),
            'create' => Pages\CreateTrackingRequest::route('/create'),
            'edit' => Pages\EditTrackingRequest::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('user', function (Builder $query) {
            $query->where('id', auth()->id());
        })->withCount('trackingAlerts', 'stocks');
    }
}
