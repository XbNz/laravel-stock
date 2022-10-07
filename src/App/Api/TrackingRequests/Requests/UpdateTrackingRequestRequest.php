<?php

declare(strict_types=1);

namespace App\Api\TrackingRequests\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTrackingRequestRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['string', 'max:255'],
            'update_interval' => ['integer', 'min:30'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
