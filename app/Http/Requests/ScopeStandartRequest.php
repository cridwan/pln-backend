<?php

namespace App\Http\Requests;

use App\Enums\ScopeStandartTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScopeStandartRequest extends FormRequest
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
            'scope_standart_uuid' => ['required', Rule::exists('transaction.scope_standarts', 'uuid')],
            'note' => 'nullable',
            'category' => ['required', Rule::enum(ScopeStandartTypeEnum::class)],
            'color' => 'nullable'
        ];
    }
}
