<?php

namespace App\Api\Stocks\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'update_interval' => ['required', 'integer', 'min:30'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'update_interval.required' => 'Update interval is required',
            'update_interval.integer' => 'Update interval must be an integer',
            'update_interval.min' => 'Update interval must be at least 30 seconds',
        ];
    }
}
