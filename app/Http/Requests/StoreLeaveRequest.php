<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\LeaveRequest;


class StoreLeaveRequest extends FormRequest
{
    public function authorize():bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in([LeaveRequest::TYPE_ANNUAL, LeaveRequest::TYPE_SICK])],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
            'attachment' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:4112'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Jenis cuti harus dipilih',
            'start_date.after_or_equal' => 'Tanggal mulai cuti harus hari ini atau setelahnya',
            'end_date.after_or_equal' => 'Tanggal akhir cuti harus sama dengan atau setelah tanggal mulai',
            'reason.min' => 'Alasan harus minimal 5 karakter',
            'reason.max' => 'Alasan harus maksimal 500 karakter',
            'attachment.mimes' => 'Lampiran harus berupa pdf, jpg, jpeg, png',
            'attachment.max' => 'Lampiran tidak boleh lebih besar dari 4MB',
        ];
    }
}
