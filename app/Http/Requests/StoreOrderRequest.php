<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'restaurant_id' => [
                'required',
                'integer',
                'exists:restaurants,id',
            ],

            'channel' => [
                'required',
                Rule::in([
                    'dine_in',
                    'delivery',
                ]),
            ],

            'customer_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'customer_phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'delivery_address' => [
                'nullable',
                'string',
            ],

            'created_by_staff_id' => [
                'nullable',
                'integer',
                'exists:staff,id',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.menu_item_id' => [
                'required',
                'integer',
                'exists:menu_items,id',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'items.*.notes' => [
                'nullable',
                'string',
            ],
        ];
    }
}
