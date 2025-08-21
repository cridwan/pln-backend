<?php

namespace App\Http\Requests\Transaction;

use App\Enums\ConnectionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CloneScopeRequest extends FormRequest
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
            'scope_standart_uuid' => ['required', Rule::exists(ConnectionEnum::GLOBAL->value . '.scope_standarts', 'uuid')],
            'project_uuid' => ['required', Rule::exists(ConnectionEnum::TRANSACTION->value . '.projects', 'uuid')],
        ];
    }
}
