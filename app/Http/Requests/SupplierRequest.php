<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SupplierRequest extends FormRequest
{

    public function rules() : array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'nullable|email|max:255|unique:suppliers,email,' . $this->id,
            'tenant_id' => 'required|string|exists:tenants,id',
        ];
    }

    public function failedValidation(Validator $validator){
        
        throw new HttpResponseException(response()->json(
            [
                'success'=>false,
                'error'=>true,
                'message'=>'Erreur de validation',
                'ErrorList'=>$validator->errors()
            ]
        ));
    }

    // Définit les messages personnalisés
    public function messages()
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'address.required' => 'L\'adresse est obligatoire.',
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            // 'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail doit être valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            // 'user_id.required' => 'L\'identifiant utilisateur est obligatoire.',
            // 'user_id.integer' => 'L\'identifiant utilisateur doit être un entier.',
            // 'user_id.exists' => 'L\'identifiant utilisateur doit exister dans la table users.',
        ];
    }
}
