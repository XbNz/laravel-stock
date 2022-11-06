<?php

declare(strict_types=1);

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
use Psl\Type;

class TrackingAlertController
{
    public function __construct(private readonly Gate $gate)
    {
    }

    public function index(Request $request): JsonResource
    {
        Assert::notNull($request->user());
        return TrackingAlertResource::collection(
            $request->user()->trackingAlerts()->cursorPaginate(20)
        );
    }

    public function store(
        CreateTrackingAlertRequest $request,
        CreateTrackingAlertAction $trackingAlertAction,
    ): JsonResource {
        $sanitized = Type\shape([
            'alert_channel_uuid' => Type\string(),
            'percentage_trigger' => Type\int(),
            'availability_trigger' => Type\bool(),
        ])->coerce($request->safe());

        Assert::notNull($request->user());

        return TrackingAlertResource::make(
            ($trackingAlertAction)(
                new CreateTrackingAlertData(
                    Uuid::fromString($sanitized['alert_channel_uuid']),
                    $sanitized['percentage_trigger'],
                    $sanitized['availability_trigger'],
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

        $sanitized = Type\shape([
            'alert_channel_uuid' => Type\optional(Type\string()),
            'percentage_trigger' => Type\optional(Type\int()),
            'availability_trigger' => Type\optional(Type\bool()),
        ])->coerce($request->safe());

        return TrackingAlertResource::make(
            ($trackingAlertAction)(
                new UpdateTrackingAlertData(
                    isset($sanitized['alert_channel_uuid'])
                        ? Uuid::fromString($sanitized['alert_channel_uuid'])
                        : null,
                    $sanitized['percentage_trigger'] ?? null,
                    $sanitized['availability_trigger'] ?? null,
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
