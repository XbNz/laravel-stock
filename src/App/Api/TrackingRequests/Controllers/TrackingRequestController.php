<?php

declare(strict_types=1);

namespace App\Api\TrackingRequests\Controllers;

use App\Api\TrackingRequests\Requests\CreateTrackingRequestRequest;
use App\Api\TrackingRequests\Requests\UpdateTrackingRequestRequest;
use App\Api\TrackingRequests\Resources\TrackingRequestResource;
use Domain\TrackingRequests\Actions\CreateTrackingRequestAction;
use Domain\TrackingRequests\Actions\DestroyTrackingRequestAction;
use Domain\TrackingRequests\Actions\UpdateTrackingRequestAction;
use Domain\TrackingRequests\DTOs\CreateTrackingRequestData;
use Domain\TrackingRequests\DTOs\UpdateTrackingRequestData;
use Domain\TrackingRequests\Models\TrackingRequest;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\ItemNotFoundException;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Webmozart\Assert\Assert;

class TrackingRequestController
{
    public function __construct(private readonly Gate $gate)
    {
    }

    public function index(Request $request): JsonResource
    {
        /** @phpstan-ignore-next-line  */
        $trackingRequests = QueryBuilder::for($request->user()->trackingRequests())
            ->allowedFilters(['store'])
            ->cursorPaginate(20);

        return TrackingRequestResource::collection($trackingRequests);
    }

    public function show(TrackingRequest $trackingRequest): JsonResource
    {
        $gate = $this->gate->inspect('view', $trackingRequest);

        if ($gate->denied()) {
            Assert::integer($gate->code());
            abort($gate->code());
        }

        return TrackingRequestResource::make($trackingRequest->load('trackingAlerts'));
    }

    public function store(
        CreateTrackingRequestRequest $request,
        CreateTrackingRequestAction $trackingRequestAction
    ): JsonResponse|JsonResource {
        try {
            $trackingRequest = ($trackingRequestAction)(
                new CreateTrackingRequestData(
                    $request->get('name'),
                    new Uri($request->get('url')),
                    $request->get('update_interval')
                ),
            $request->user()
            );
        } catch (ItemNotFoundException) {
            return ResponseFacade::json([
                'errors' => [
                    'url' => 'Unsupported store.',
                ],
            ], SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        return TrackingRequestResource::make($trackingRequest);
    }

    public function update(
        TrackingRequest $trackingRequest,
        UpdateTrackingRequestRequest $request,
        UpdateTrackingRequestAction $updateTrackingRequest,
    ): JsonResource {
        $gate = $this->gate->inspect('update', $trackingRequest);

        if ($gate->denied()) {
            Assert::integer($gate->code());
            abort($gate->code());
        }

        $trackingRequest = ($updateTrackingRequest)(
            new UpdateTrackingRequestData(
                $request->get('name'),
                $request->get('update_interval')
            ),
        $trackingRequest
        );

        return TrackingRequestResource::make($trackingRequest);
    }

    public function destroy(
        TrackingRequest $trackingRequest,
        DestroyTrackingRequestAction $destroyTrackingRequest
    ): Response {
        $gate = $this->gate->inspect('delete', $trackingRequest);

        if ($gate->denied()) {
            Assert::integer($gate->code());
            abort($gate->code());
        }

        ($destroyTrackingRequest)($trackingRequest);

        return ResponseFacade::noContent(SymfonyResponse::HTTP_NO_CONTENT);
    }
}
