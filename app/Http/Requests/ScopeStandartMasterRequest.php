<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScopeStandartMasterRequest extends FormRequest
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
            "name" => "required",
            "link" => "nullable",
            "additional_scope_uuid" => "nullable|exists:additional_scopes,uuid",
            "inspection_type_uuid" => "nullable|exists:inspection_types,uuid",
            "sub_bidang_uuid" => "required|exists:sub_bidangs,uuid",
            "sequence_uuid" => "required|exists:sequences,uuid"
        ];
    }
}
