<?php

namespace App\Http\Requests;

use App\Enums\GroupEnum;
use App\Enums\OperatorEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaginationRequest extends FormRequest
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
            'perPage' => 'required',
            'currentPage' => 'required',
            'search' => 'nullable',
            'filter.*.group' => ['nullable', Rule::enum(GroupEnum::class)],
            'filter.*.operator' => ['nullable', Rule::enum(OperatorEnum::class)],
            'filter.*.column' => ['nullable', 'string'],
            'filter.*.value' => ['nullable'],
            'filters' => 'nullable',
            'order' => 'nullable'
        ];
    }
}
