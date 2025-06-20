<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class UploadPaymentRequest extends FormRequest
{
    // public function authorize()
    // {
    //     return true; // Điều chỉnh nếu cần phân quyền
    // }

    public function rules()
    {
        return [
            'paymentProof' => 'required|file|mimes:jpeg,png,jpg,pdf|max:10240', // Tối đa 10MB
        ];
    }
}