<?php

declare(strict_types=1);

namespace App\Api\TrackingRequests\Requests;

use Domain\Alerts\Rules\UserOwnsTrackingAlertRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTrackingRequestRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            // url and user_id are unique together
            'url' => [
                'required',
                'active_url',
                Rule::unique('tracking_requests')
                    ->where(fn (Builder $query) => $query->where('user_id', $this->user()->id))
            ],
            'update_interval' => ['required', 'integer', 'min:30'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
