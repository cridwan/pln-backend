<?php

namespace App\Http\Requests\Transaction;

use App\Enums\ConnectionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CloneAdditionalScopeRequest extends FormRequest
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
            'additional_scope_uuid' => ['required', Rule::exists(ConnectionEnum::GLOBAL ->value . '.additional_scopes', 'uuid')],
            'project_uuid' => ['required', Rule::exists(ConnectionEnum::TRANSACTION->value . '.projects', 'uuid')],
        ];
    }
}
