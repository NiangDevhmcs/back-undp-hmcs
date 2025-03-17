<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number_one' => 'required|string|max:20',
            'phone_number_two' => 'nullable|string|max:20',
            'email' => 'required|email|unique:users,email,' .$this->user_id,
            // 'status' => 'required',
            'address' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female',
            'role_id' => 'required|exists:roles,id',
            'profile_photo_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // 'campus_id' => 'required|exists:campuses,id',
        ];

        return $rules;
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


    public function messages(): array
    {
        return [
            'first_name.required' => 'Le prénom est requis',
            'first_name.string' => 'Le prénom doit être une chaîne de caractères',
            'first_name.max' => 'Le prénom ne doit pas dépasser 255 caractères',
            
            'last_name.required' => 'Le nom est requis',
            'last_name.string' => 'Le nom doit être une chaîne de caractères',
            'last_name.max' => 'Le nom ne doit pas dépasser 255 caractères',
            
            'phone_number_one.required' => 'Le numéro de téléphone principal est requis',
            'phone_number_one.string' => 'Le numéro de téléphone doit être une chaîne de caractères',
            'phone_number_one.max' => 'Le numéro de téléphone ne doit pas dépasser 20 caractères',
            
            'phone_number_two.string' => 'Le second numéro de téléphone doit être une chaîne de caractères',
            'phone_number_two.max' => 'Le second numéro de téléphone ne doit pas dépasser 20 caractères',
            
            'email.required' => 'L\'adresse email est requise',
            'email.email' => 'L\'adresse email doit être valide',
            'email.unique' => 'Cette adresse email est déjà utilisée',
            
            'address.string' => 'L\'adresse doit être une chaîne de caractères',
            'address.max' => 'L\'adresse ne doit pas dépasser 255 caractères',
            
            'gender.required' => 'Le genre est requis',
            'gender.in' => 'Le genre doit être soit masculin soit féminin',
            
            'role_id.required' => 'Le rôle est requis',
            'role_id.exists' => 'Le rôle sélectionné n\'existe pas',
            // 'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ];
    }
}
