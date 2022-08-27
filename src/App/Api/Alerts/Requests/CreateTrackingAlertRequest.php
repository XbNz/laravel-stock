<?php

namespace App\Api\Alerts\Requests;

use Domain\Alerts\Rules\AlertChannelMustBeVerifiedRule;
use Domain\Alerts\Rules\UserOwnsAlertChannelRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateTrackingAlertRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'alert_channel_uuid' => [
                'required',
                'uuid',
                'exists:alert_channels,uuid',
                'bail',
                new UserOwnsAlertChannelRule($this->user()),
                'bail',
                new AlertChannelMustBeVerifiedRule,
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
