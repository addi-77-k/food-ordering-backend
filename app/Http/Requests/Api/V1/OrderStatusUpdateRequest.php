<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class OrderStatusUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                'in:pending,confirmed,preparing,ready,out_for_delivery,completed,cancelled',
            ],
            'comment' => ['nullable', 'string', 'max:500'],
        ];
    }
}
