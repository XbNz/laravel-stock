<?php

declare(strict_types=1);

namespace App\Api\Alerts\Requests;

use Domain\Alerts\Enums\AlertChannel;
use Domain\Alerts\Rules\ValueChannelRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class CreateAlertChannelRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => [
                'required',
                'string',
                'bail',
                Rule::in(Collection::make(AlertChannel::cases())->pluck('value')),
                Rule::unique('alert_channels', 'type')
                    ->where('value', $this->get('value')),
            ],
            'value' => [
                'required',
                'string',
                'bail',
                new ValueChannelRule(
                    AlertChannel::tryFrom($this->get('type'))
                ),
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
