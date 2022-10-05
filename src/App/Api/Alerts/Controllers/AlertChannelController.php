<?php

declare(strict_types=1);

namespace App\Api\Alerts\Controllers;

use App\Api\Alerts\Requests\CreateAlertChannelRequest;
use App\Api\Alerts\Resources\AlertChannelResource;
use Domain\Alerts\Actions\CreateAlertChannelAction;
use Domain\Alerts\DTOs\AlertChannelData;
use Domain\Alerts\Enums\AlertChannel as AlertChannelEnum;
use Domain\Alerts\Models\AlertChannel;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Webmozart\Assert\Assert;

class AlertChannelController
{
    public function __construct(private readonly Gate $gate)
    {
    }

    public function index(Request $request): JsonResource
    {
        return AlertChannelResource::collection($request->user()->alertChannels()->paginate(20));
    }

    public function store(
        CreateAlertChannelRequest $request,
        CreateAlertChannelAction $alertChannelAction,
    ): JsonResource {
        return AlertChannelResource::make(
            ($alertChannelAction)(
                new AlertChannelData(
                    AlertChannelEnum::from($request->get('type')),
                    $request->get('value'),
                ),
            $request->user(),
            )
        );
    }

    public function show(AlertChannel $alertChannel): JsonResource
    {
        $gate = $this->gate->inspect('view', $alertChannel);

        if ($gate->denied()) {
            Assert::integer($gate->code());
            abort($gate->code());
        }

        return AlertChannelResource::make($alertChannel);
    }

    public function destroy(AlertChannel $alertChannel): Response
    {
        $gate = $this->gate->inspect('view', $alertChannel);

        if ($gate->denied()) {
            Assert::integer($gate->code());
            abort($gate->code());
        }

        $alertChannel->delete();

        return ResponseFacade::noContent(SymfonyResponse::HTTP_NO_CONTENT);
    }
}
