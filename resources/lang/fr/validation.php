<?php
// resources/lang/fr/validation.php

return [
    'required' => "Le champ :attribute est obligatoire.",
    'string' => "Le champ :attribute doit être une chaîne de caractères.",
    'max' => [
        'string' => "Le champ :attribute ne doit pas dépasser :max caractères.",
    ],
    'exists' => "Le :attribute sélectionné est invalide.",

        'first_name.required' => 'Le prénom est requis.',
        'first_name.min' => 'Le prénom doit contenir au moins 2 caractères.',
        'last_name.required' => 'Le nom est requis.',
        'last_name.min' => 'Le nom doit contenir au moins 2 caractères.',
        'email.required' => 'L\'adresse email est requise.',
        'email.email' => 'Veuillez saisir une adresse email valide.',
        'email.min' => 'L\'email doit contenir au moins 8 caractères.',
        'email.max' => 'L\'email ne peut pas dépasser 254 caractères.',
        'email.regex' => 'Veuillez entrer un format d\'email valide.',
        'email.unique' => 'Cette adresse email est déjà enregistrée.',
        'password.required' => 'Le mot de passe est requis.',
        'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        'password_confirmation.required' => 'Veuillez confirmer votre mot de passe.',
        'role_id.required' => 'Le rôle est requis.',
        'role_id.exists' => 'Le rôle sélectionné est invalide.',
];
