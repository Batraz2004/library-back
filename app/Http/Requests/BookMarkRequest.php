<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookMarkRequest extends FormRequest
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
            'book_id' => 'required|integer|exists:books,id',
            'quantity' => 'required|integer',
        ];
    }

    public function getData()
    {
        $data = [
            'book_id' => $this->book_id,
            'quantity' => $this->quantity,
        ];

        return $data;
    }
}
