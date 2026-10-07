<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $restaurantId = $this->user()->restaurant_id;

        return [
            'customer_name'    => ['sometimes', 'nullable', 'string', 'max:255'],
            'customer_phone'   => ['sometimes', 'nullable', 'string', 'max:20'],
            'delivery_address' => ['sometimes', 'nullable', 'string', 'max:1000'],

            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.menu_item_id' => [
                'required_with:items',
                'integer',
                'distinct',
                Rule::exists('menu_items', 'id')
                    ->where('restaurant_id', $restaurantId)
                    ->where('is_available', true),
            ],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1', 'max:100'],
            'items.*.notes'    => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.min'                     => 'لازم تضيف صنف واحد على الأقل.',
            'items.*.menu_item_id.exists'   => 'الصنف غير موجود أو غير متاح حالياً.',
            'items.*.menu_item_id.distinct' => 'الصنف متكرر، زوّد الكمية بدل ما تكرره.',
        ];
    }
}
