<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AnalyticsRangeRequest extends FormRequest
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
        return [
            'from' => ['required', 'date'],
            'to'   => ['required', 'date', 'after_or_equal:from'],
        ];
    }

    public function messages(): array
    {
        return [
            'from.required'    => 'تاريخ البداية (from) مطلوب',
            'to.required'      => 'تاريخ النهاية (to) مطلوب',
            'from.date'        => 'تاريخ البداية غير صالح',
            'to.date'          => 'تاريخ النهاية غير صالح',
            'to.after_or_equal' => 'تاريخ النهاية لازم يكون بعد أو يساوي تاريخ البداية',
        ];
    }

    public function from(): Carbon
    {
        return Carbon::parse($this->validated('from'))->startOfDay();
    }

    public function to(): Carbon
    {
        return Carbon::parse($this->validated('to'))->endOfDay();
    }
}
