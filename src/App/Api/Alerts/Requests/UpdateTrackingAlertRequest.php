<?php

declare(strict_types=1);

namespace App\Api\Alerts\Requests;

use Domain\Alerts\Rules\AlertChannelMustBeVerifiedRule;
use Domain\Alerts\Rules\UserOwnsAlertChannelRule;
use Illuminate\Foundation\Http\FormRequest;
use Webmozart\Assert\Assert;

class UpdateTrackingAlertRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        Assert::notNull($this->user());
        return [
            'alert_channel_uuid' => [
                'uuid',
                'exists:alert_channels,uuid',
                'bail',
                new UserOwnsAlertChannelRule($this->user()),
                'bail',
                new AlertChannelMustBeVerifiedRule(),
            ],
            'percentage_trigger' => ['integer', 'min:1', 'max:100'],
            'availability_trigger' => ['boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
