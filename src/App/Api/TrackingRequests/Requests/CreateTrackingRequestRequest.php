<?php

declare(strict_types=1);

namespace App\Api\TrackingRequests\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Webmozart\Assert\Assert;

class CreateTrackingRequestRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        Assert::notNull($this->user());

        return [
            'name' => ['required', 'string', 'max:255'],
            'url' => [
                'required',
                'active_url',
                Rule::unique('tracking_requests')
                    ->where(fn (Builder $query) => $query->where('user_id', $this->user()->id)),
            ],
            'update_interval' => ['required', 'integer', 'min:30'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
