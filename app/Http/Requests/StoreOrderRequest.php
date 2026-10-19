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
        $restaurantId = $this->user()->restaurant_id;

        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('menu_items', 'id')
                    ->where('restaurant_id', $restaurantId)
                    ->where('is_available', true),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.notes'    => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'                => 'لازم تضيف صنف واحد على الأقل.',
            'items.min'                     => 'لازم تضيف صنف واحد على الأقل.',
            'items.*.menu_item_id.exists'   => 'الصنف غير موجود أو غير متاح حالياً.',
            'items.*.menu_item_id.distinct' => 'الصنف متكرر، زوّد الكمية بدل ما تكرره.',
        ];
    }
}
