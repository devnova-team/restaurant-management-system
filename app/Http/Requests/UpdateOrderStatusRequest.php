<?php
// app/Http/Requests/UpdateOrderStatusRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
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
                Rule::in([
                    'received',
                    'preparing',
                    'ready',
                    'served',
                    'out_for_delivery',
                    'delivered',
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'حالة الطلب مطلوبة.',
            'status.in'       => 'حالة الطلب غير صحيحة.',
        ];
    }
}
