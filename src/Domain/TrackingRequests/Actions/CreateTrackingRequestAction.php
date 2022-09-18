<?php

namespace Domain\TrackingRequests\Actions;

use Domain\Stores\Actions\ParseStoreByLinkAction;
use Domain\TrackingRequests\DTOs\CreateTrackingRequestData;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\Users\Models\User;

class CreateTrackingRequestAction
{
    public function __construct(
        private readonly InferTrackingTypeForStoreAction $trackingTypeForStoreAction,
        private readonly ParseStoreByLinkAction $parseStoreByLinkAction,
    ) {
    }

    public function __invoke(CreateTrackingRequestData $data, User $user): TrackingRequest
    {
        $store = ($this->parseStoreByLinkAction)($data->url);

        return TrackingRequest::query()->firstOrCreate([
            'user_id' => $user->id,
            'url' => (string) $data->url,
            'update_interval' => $data->updateInterval,
        ], [
            'user_id' => $user->id,
            'url' => (string) $data->url,
            'update_interval' => $data->updateInterval,
            'store' => $store,
            'tracking_type' => ($this->trackingTypeForStoreAction)($store, $data->url),
        ]);
    }
}
