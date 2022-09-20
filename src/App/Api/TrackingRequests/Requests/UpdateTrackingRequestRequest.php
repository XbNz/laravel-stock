<?php

namespace App\Api\TrackingRequests\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTrackingRequestRequest extends FormRequest
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
}
