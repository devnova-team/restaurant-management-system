<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;



class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        $restaurantId = $this->user()->restaurant_id;

        return [
            'channel' => ['required', Rule::in(['dine_in', 'delivery'])],


            'customer_name'    => ['required_if:channel,delivery', 'nullable', 'string', 'max:255'],
            'customer_phone'   => ['required_if:channel,delivery', 'nullable', 'string', 'max:20'],
            'delivery_address' => ['required_if:channel,delivery', 'nullable', 'string', 'max:1000'],

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
            'items.*.menu_item_id.exists'   => 'الصنف غير موجود أو غير متاح حالياً.',
            'items.*.menu_item_id.distinct' => 'الصنف متكرر، زوّد الكمية بدل ما تكرره.',
        ];
    }

    }

