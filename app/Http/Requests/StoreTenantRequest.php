<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTenantRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Autoriser la requête
    }

    public function rules()
    {
        return [
            // Validation pour le tenant
            'name' => 'required|string|max:255',
            'campus_limit' => 'required|integer|min:1',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'slogan' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'short_name' => 'nullable|string|max:255',

            // Validation pour le plan d'abonnement
            'amount_subscription' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'amount_subscription_paid' => 'nullable',
            'amount_subscription_no_paid' => 'nullable',
            'next_billing_date'=> 'nullable',

            // // Validation pour le propriétaire
            'owner_first_name' => 'required|string|max:255',
            'owner_last_name' => 'required|string|max:255',
            'owner_email' => 'required|email|unique:users,email',
            // 'matricule' => 'required|string|unique:users,matricule',
            // 'role_id' => 'required|integer|unique:roles,id',
            'phone_number_one' => 'required|string',
            'phone_number_two' => 'nullable|string',
            // 'owner_password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Le nom du tenant est obligatoire.',
            'campus_limit.required' => 'La limite des campus est obligatoire.',
            'campus_limit.integer' => 'La limite des campus doit être un nombre entier.',
            'logo.image' => 'Le logo doit être une image valide.',
            'website.url' => 'Le site web doit être une URL valide.',
            'amount_subscription.required' => "Le montant de l'abonnement est obligatoire.",
            'billing_cycle.required' => "Le cycle de facturation est obligatoire.",
            'start_date.required' => "La date de début est obligatoire.",
            'end_date.after_or_equal' => "La date de fin doit être ultérieure ou égale à la date de début.",
            'owner_first_name.required' => 'Le prénom du propriétaire est obligatoire.',
            'owner_email.unique' => "L'email du propriétaire est déjà utilisé.",
            // 'owner_password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
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
