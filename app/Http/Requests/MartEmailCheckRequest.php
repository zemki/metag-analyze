<?php

namespace App\Http\Requests;

use App\Rules\StrictEmail;
use Illuminate\Foundation\Http\FormRequest;

class MartEmailCheckRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Check raw request body for null bytes before JSON parsing
        $rawContent = $this->getContent();

        if (strpos($rawContent, "\0") !== false || strpos($rawContent, '\u0000') !== false) {
            // Force validation to fail by setting invalid email
            $this->merge([
                'email' => null,
                '_null_byte_detected' => true,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'max:255', new StrictEmail],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        if ($this->has('_null_byte_detected')) {
            return [
                'email.required' => 'The email must be a valid email address.',
                'email.*' => 'The email must be a valid email address.',
            ];
        }

        return [];
    }
}
