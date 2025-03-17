<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductRequest extends FormRequest
{

    // Autorise la requête
    public function authorize()
    {
        return true; // Changez ceci selon votre logique d'autorisation
    }

    // Définit les règles de validation
    public function rules() : array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'stock_quantity' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'limit' => 'required|numeric|min:0',
            'require_serial_number' => 'nullable|boolean',
            'category_id' => 'required',
            'campus_id' => 'required',
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
            'name.required' => 'Le nom du produit est obligatoire.',
            'name.string' => 'Le nom du produit doit être une chaîne de caractères.',
            'name.max' => 'Le nom du produit ne peut pas dépasser 255 caractères.',
            'stock_quantity.required' => 'La quantité en stock est obligatoire.',
            'stock_quantity.integer' => 'La quantité en stock doit être un nombre entier.',
            'stock_quantity.min' => 'La quantité en stock doit être au moins de 0.',
            'unit_price.required' => 'Le prix unitaire est obligatoire.',
            'unit_price.numeric' => 'Le prix unitaire doit être un nombre.',
            'limit.required' => 'Le seuil est obligatoire.',
            'limit.numeric' => 'Le seuil doit être un nombre.',
            'unit_price.min' => 'Le prix unitaire doit être au moins de 0.',
            'user_id.required' => 'L\'identifiant utilisateur est obligatoire.',
            'user_id.integer' => 'L\'identifiant utilisateur doit être un entier.',
            'user_id.exists' => 'L\'identifiant utilisateur doit exister dans la table users.',
        ];
    }
}
