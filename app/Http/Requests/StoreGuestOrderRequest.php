<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGuestOrderRequest extends FormRequest
{
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
        return [
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'regex:/^01[0-2,5]{1}[0-9]{8}$/'],
            'delivery_address' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => [
                'required',
                'integer',
                Rule::exists('menu_items', 'id')
                    ->where('restaurant_id', $this->route('restaurant')?->id)
                    ->where('is_available', true),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'اسم العميل مطلوب',
            'customer_phone.required' => 'رقم الهاتف مطلوب',
            'customer_phone.regex' => 'رقم الهاتف غير صحيح',
            'delivery_address.required' => 'عنوان التوصيل مطلوب',
            'items.required' => 'يجب إضافة صنف واحد على الأقل',
            'items.*.menu_item_id.exists' => 'الصنف غير متاح في هذا المطعم',
        ];
    }
}
