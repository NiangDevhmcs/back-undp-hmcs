<?php

return [
    'required' => 'Le champ :attribute est obligatoire.',
    'email' => 'Le champ :attribute doit être une adresse email valide.',
    'string' => 'Le champ :attribute doit être une chaîne de caractères.',
    'min' => [
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],
    'max' => [
        'string' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
    ],
    'boolean' => 'Le champ :attribute doit être vrai ou faux.',
    'exists' => 'Le :attribute sélectionné est invalide.',

    'attributes' => [
        'email' => 'email',
        'password' => 'mot de passe',
        'remember_me' => 'se souvenir de moi',
    ],
];
