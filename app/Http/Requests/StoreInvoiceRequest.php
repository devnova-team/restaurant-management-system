<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => [
                'required',
                'integer',
                'exists:orders,id',
                'unique:invoices,order_id',
            ],

            'payment_method' => [
                'required',
                Rule::in([
                    'cash',
                    'card',
                    'cod',
                ]),
            ],
        ];
    }
}
