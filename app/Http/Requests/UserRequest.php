<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        $rules =  [
            'name' => 'required',
            'email' => ['required', 'email', Rule::exists('master.users', 'email')],
            'password' => 'required'
        ];

        if ($this->method() == 'PUT') {
            $rules = [
                'email' => ['required', 'email', Rule::exists('master.users', 'email')->whereNot('id', $this->route('uuid'))],
                'password' => 'nullable'
            ];
        }

        return $rules;
    }
}
