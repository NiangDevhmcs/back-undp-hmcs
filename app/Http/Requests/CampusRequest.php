<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CampusRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'tenant_id' => 'required|uuid',
            'address' => 'required|string|max:255',
            'phone_one' => 'required|string|max:15',
            'phone_two' => 'nullable|string|max:15',
            'nb_user' => 'nullable|integer|min:0',
            'status' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du campus est obligatoire.',
            'name.string' => 'Le nom du campus doit être une chaîne de caractères.',
            'name.max' => 'Le nom du campus ne doit pas dépasser 255 caractères.',

            'tenant_id.required' => 'L\'identifiant du locataire est obligatoire.',
            'tenant_id.uuid' => 'L\'identifiant du locataire doit être un UUID valide.',

            'address.required' => 'L\'adresse du campus est obligatoire.',
            'address.string' => 'L\'adresse du campus doit être une chaîne de caractères.',
            'address.max' => 'L\'adresse du campus ne doit pas dépasser 255 caractères.',

            'phone_one.required' => 'Le premier numéro de téléphone est obligatoire.',
            'phone_one.string' => 'Le premier numéro de téléphone doit être une chaîne de caractères.',
            'phone_one.max' => 'Le premier numéro de téléphone ne doit pas dépasser 15 caractères.',

            'phone_two.string' => 'Le deuxième numéro de téléphone doit être une chaîne de caractères.',
            'phone_two.max' => 'Le deuxième numéro de téléphone ne doit pas dépasser 15 caractères.',

            'nb_user.integer' => 'Le nombre d\'utilisateurs doit être un entier.',
            'nb_user.min' => 'Le nombre d\'utilisateurs ne peut pas être négatif.',

            'status.required' => 'Le statut est obligatoire.',
            'status.in' => 'Le statut doit être soit "active" soit "inactive".',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(
            [
                'success' => false,
                'error' => true,
                'message' => 'Erreur de validation',
                'ErrorList' => $validator->errors()
            ],
            422
        ));
    }
}
