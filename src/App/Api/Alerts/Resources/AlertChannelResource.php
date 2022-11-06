<?php

declare(strict_types=1);

namespace App\Api\Alerts\Resources;

use Carbon\CarbonInterface;
use Domain\Alerts\Enums\AlertChannel as AlertChannelEnum;
use Domain\Alerts\Models\AlertChannel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Psl\Type;
use Webmozart\Assert\Assert;

/** @mixin AlertChannel */
class AlertChannelResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        Assert::notNull($this->created_at);
        Assert::notNull($this->updated_at);

        return [
            'uuid' => $this->uuid,
            'type' => $this->type->value,
            'value' => $this->value,
            'verified_at' => $this->verified_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];

    }
}
