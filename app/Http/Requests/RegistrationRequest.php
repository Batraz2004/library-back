<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistrationRequest extends FormRequest
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
            'email'    => ['required', 'max:255', 'email', 'unique:users'],
            'password' => ['required', 'max:255'],
            'name'     => ['required', 'max:255'],
        ];
    }

    public function getData()
    {
        $data = $this->only(['email','password','name']);

        return $data;
    }
}
