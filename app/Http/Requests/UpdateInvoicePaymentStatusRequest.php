<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoicePaymentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_status' => [
                'required',
                Rule::in([
                    'unpaid',
                    'paid',
                ]),
            ],
            'amount' => [
                'required_if:payment_status,paid',
                'numeric',
                'min:0',
            ],
        ];
    }
}
