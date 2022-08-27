<?php

namespace App\Api\Alerts\Controllers;

use App\Api\Alerts\Requests\CreateTrackingAlertRequest;
use App\Api\Alerts\Requests\UpdateTrackingAlertRequest;
use App\Api\Alerts\Resources\TrackingAlertResource;
use Domain\Alerts\Actions\CreateTrackingAlertAction;
use Domain\Alerts\Actions\UpdateTrackingAlertAction;
use Domain\Alerts\DTOs\CreateTrackingAlertData;
use Domain\Alerts\DTOs\UpdateTrackingAlertData;
use Domain\Alerts\Models\TrackingAlert;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Webmozart\Assert\Assert;

class TrackingAlertController
{
    public function __construct(private readonly Gate $gate)
    {
    }

    public function index(Request $request): JsonResource
    {
        return TrackingAlertResource::collection(
            $request->user()->trackingAlerts()->cursorPaginate(20)
        );
    }

    public function store(
        CreateTrackingAlertRequest $request,
        CreateTrackingAlertAction $trackingAlertAction,
    ): JsonResource {

        return TrackingAlertResource::make(
            ($trackingAlertAction)(
                new CreateTrackingAlertData(
                    Uuid::fromString($request->get('alert_channel_uuid')),
                    $request->get('percentage_trigger'),
                    $request->get('availability_trigger'),
                ),
                $request->user(),
            ),
        );
    }

    public function show(TrackingAlert $trackingAlert): JsonResource
    {
        $gate = $this->gate->inspect('view', $trackingAlert);

        if ($gate->denied()) {
            Assert::integer($gate->code());
            abort($gate->code());
        }

        return TrackingAlertResource::make($trackingAlert->load('trackingRequests'));
    }

    public function update(
        UpdateTrackingAlertRequest $request,
        TrackingAlert $trackingAlert,
        UpdateTrackingAlertAction $trackingAlertAction,
    ): JsonResource {

        $gate = $this->gate->inspect('update', $trackingAlert);

        if ($gate->denied()) {
            Assert::integer($gate->code());
            abort($gate->code());
        }

        return TrackingAlertResource::make(
            ($trackingAlertAction)(
                new UpdateTrackingAlertData(
                    $request->get('alert_channel_uuid') !== null
                        ? Uuid::fromString($request->get('alert_channel_uuid'))
                        : null,
                    $request->get('percentage_trigger'),
                    $request->get('availability_trigger'),
                ),
                $trackingAlert,
            ),
        );
    }

    public function destroy(TrackingAlert $trackingAlert): Response
    {
        $gate = $this->gate->inspect('delete', $trackingAlert);

        if ($gate->denied()) {
            Assert::integer($gate->code());
            abort($gate->code());
        }

        $trackingAlert->delete();

        return ResponseFacade::noContent(SymfonyResponse::HTTP_NO_CONTENT);
    }
}
