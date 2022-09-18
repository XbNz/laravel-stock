<?php

declare(strict_types=1);

namespace App\Api\TrackingRequests\Requests;

use Domain\Alerts\Rules\UserOwnsTrackingAlertRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateTrackingRequestRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'url' => ['required', 'active_url', 'unique:tracking_requests,url'],
            'update_interval' => ['required', 'integer', 'min:30'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
