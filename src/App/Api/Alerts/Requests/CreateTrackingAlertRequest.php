<?php

declare(strict_types=1);

namespace App\Api\Alerts\Requests;

use Domain\Alerts\Rules\AlertChannelMustBeVerifiedRule;
use Domain\Alerts\Rules\UserOwnsAlertChannelRule;
use Illuminate\Foundation\Http\FormRequest;
use Webmozart\Assert\Assert;

class CreateTrackingAlertRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        Assert::notNull($this->user());
        return [
            'alert_channel_uuid' => [
                'required',
                'uuid',
                'exists:alert_channels,uuid',
                'bail',
                new UserOwnsAlertChannelRule($this->user()),
                'bail',
                new AlertChannelMustBeVerifiedRule(),
            ],
            'percentage_trigger' => ['integer', 'min:1', 'max:100', 'required_without:availability_trigger'],
            'availability_trigger' => ['boolean', 'required_without:percentage_trigger'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
