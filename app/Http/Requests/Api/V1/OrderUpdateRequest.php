<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class OrderUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delivery_address' => ['nullable', 'string', 'max:500'],
            'payment_status' => ['nullable', 'string', 'in:pending,paid,failed,refunded'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
