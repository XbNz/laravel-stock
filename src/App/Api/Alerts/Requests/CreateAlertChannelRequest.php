<?php

namespace App\Api\Alerts\Requests;

use Domain\Alerts\Enums\AlertChannel;
use Domain\Alerts\Rules\ValueChannelRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class CreateAlertChannelRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => [
                'required',
                Rule::in(Collection::make(AlertChannel::cases())->pluck('value')),
                Rule::unique('alert_channels', 'type')
                    ->where('value', $this->get('value')),
            ],
            'value' => [
                'required',
                new ValueChannelRule(
                    AlertChannel::tryFrom($this->request->get('type'))
                )
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'type.unique' => 'The type and value combination must be unique',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
