<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|min:2|max:60',
            'email' => 'required|email:rfc,dns|max:254',
            'phone' => ['required', 'regex:/^\+380[0-9]{9}$/'],
            'position_id' => 'required|integer|exists:positions,id',
            'photo' => 'required|mimes:jpg,jpeg|max:5120',
        ];
    }
    public function messages()
    {
        return [
            'photo.max' => 'The photo may not be greater than 5 Mbytes.',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'fails' => $validator->errors()->toArray(),
        ], 422));
    }

}
