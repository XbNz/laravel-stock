<?php

namespace App\Api\TrackingRequests\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTrackingRequestRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'url' => ['required', 'active_url', 'unique:tracking_requests,url'],
            'update_interval' => ['required', 'integer', 'min:30'],
            'alert_uuid' => ['required', 'uuid', 'exists:alerts,uuid'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
