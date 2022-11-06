<?php

declare(strict_types=1);

namespace App\Api\Alerts\Requests;

use Domain\Alerts\Enums\AlertChannel;
use Domain\Alerts\Rules\ValueChannelRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Psl\Type;

class CreateAlertChannelRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $sanitized = Type\shape([
            'type' => Type\string(),
            'value' => Type\string()
        ])->coerce($this->all());

        return [
            'type' => [
                'required',
                'string',
                'bail',
                Rule::in(Collection::make(AlertChannel::cases())->pluck('value')),
                Rule::unique('alert_channels', 'type')
                    ->where('value', $sanitized['value'])
            ],
            'value' => [
                'required',
                'string',
                'bail',
                new ValueChannelRule(
                    AlertChannel::tryFrom($sanitized['type'])
                ),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
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
