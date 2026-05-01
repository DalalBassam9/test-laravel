<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'secondaryPhone' => 'nullable|string|max:20',
            'address' => 'required|string',
            'district' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'cityId' => 'nullable|exists:cities,id',
            'default' => 'nullable|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'firstName.required' => 'الاسم الأول مطلوب',
            'firstName.max' => 'الاسم الأول يجب أن لا يتجاوز 255 حرف',
            'lastName.required' => 'الاسم الأخير مطلوب',
            'lastName.max' => 'الاسم الأخير يجب أن لا يتجاوز 255 حرف',
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.max' => 'رقم الهاتف يجب أن لا يتجاوز 20 رقم',
            'secondaryPhone.max' => 'رقم الهاتف الثانوي يجب أن لا يتجاوز 20 رقم',
            'address.required' => 'العنوان مطلوب',
            'district.max' => 'المنطقة يجب أن لا تتجاوز 255 حرف',
            'cityId.exists' => 'المدينة المختارة غير موجودة'
        ];
    }
}
