<?php

namespace App\Orders\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReserveTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'event_id' => ['required', 'integer', 'exists:events,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'event_id.required' => 'An event must be selected.',
            'event_id.exists' => 'The selected event does not exist.',
        ];
    }
}
