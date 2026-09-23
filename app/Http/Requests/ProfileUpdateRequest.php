<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\RwandaLocations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            ...RwandaLocations::validationRules(),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach (RwandaLocations::validateHierarchy($this->all()) as $field => $messages) {
                foreach ($messages as $message) {
                    $validator->errors()->add($field, $message);
                }
            }
        });
    }

    protected function passedValidation(): void
    {
        if ($this->input('country') !== RwandaLocations::DEFAULT_COUNTRY) {
            $this->merge([
                'province' => null,
                'district' => null,
                'sector' => null,
            ]);
        }
    }
}
