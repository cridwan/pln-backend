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
            "category" => "required",
            "additional_scope_uuid" => "nullable",
            "inspection_type_uuid" => "required",
            "details" => "required"
        ];
    }
}
