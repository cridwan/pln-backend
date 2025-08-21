<?php

namespace App\Http\Requests\Transaction;

use App\Enums\ConnectionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClonePartRequest extends FormRequest
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
            'manpower_uuid' => ['required', Rule::exists(ConnectionEnum::GLOBAL->value . '.part_stds')],
            'activity_uuid' => ['required', Rule::exists(ConnectionEnum::TRANSACTION->value . '.activities')]
        ];
    }
}
