<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ResponseServer
{
    /**
     * Valide les données de connexion et renvoie les messages d'erreur appropriés
     *
     * @param Request $request La requête contenant les données à valider
     * @return array|Illuminate\Http\JsonResponse Les données validées ou une réponse d'erreur
     */
    public static function validateLoginData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8',
            'remember_me' => 'nullable|boolean'
        ], [
            'email.required' => trans('validation.required', ['attribute' => 'email']),
            'email.email' => trans('validation.email', ['attribute' => 'email']),
            'email.max' => trans('validation.max.string', ['attribute' => 'email', 'max' => 255]),
            'password.required' => trans('validation.required', ['attribute' => 'mot de passe']),
            'password.string' => trans('validation.string', ['attribute' => 'mot de passe']),
            'password.min' => trans('validation.min.string', ['attribute' => 'mot de passe', 'min' => 8]),
            'remember_me.boolean' => trans('validation.boolean', ['attribute' => 'se souvenir de moi'])
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        return $validator->validated();
    }

    public static function requireOtpVerification($user, $expireIn){
        return response()->json([
            'status' => 'pending_otp',
            'message' => trans('message.opt_send'),
            'requires_otp' => true,
            'email' => $user->email,
            'otp_expires_in' => $expireIn
        ]);
    }

    public static function otpVerification($user, $expireIn){
        return response()->json([
            'status' => true,
            'requires_otp' => $user->requires_otp,
            'email' => $user->email,
            'message' => trans('message.otp_send'),
            'otp_expires_in' => $expireIn
        ]);
    }

    public static function incorrectCurrentPassword(){
        return response()->json([
            'success' => false,
            'error' => true,
            'message' => trans('message.incorrect_current_password'),
        ], 422);
    }

    public static function validatedRegister(array $data)
    {
        $validator = Validator::make($data, [
            'first_name' => ['required', 'string', 'min:2'],
            'last_name' => ['required', 'string', 'min:2'],
            'email' => [
                'required',
                'string',
                'email',
                'min:8',
                'max:254',
                'regex:/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/',
                'unique:users,email'
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
            'role_id' => ['nullable', 'exists:roles,id'],
        ], [
            'first_name.required' => trans('validation.first_name.required'),
            'first_name.min' => trans('validation.first_name.min'),
            'last_name.required' => trans('validation.last_name.required'),
            'last_name.min' => trans('validation.last_name.min'),
            'email.required' => trans('validation.email.required'),
            'email.email' => trans('validation.email'),
            'email.min' => trans('validation.email.min'),
            'email.max' => trans('validation.email.max'),
            'email.regex' => trans('validation.email.regex'),
            'email.unique' => trans('validation.email.unique'),
            'password.required' => trans('validation.required'),
            'password.min' => trans('validation.password.min'),
            'password.confirmed' => trans('validation.password.confirmed'),
            'password_confirmation.required' => trans('validation.password_confirmation.required'),
            'role_id.required' => trans('validation.required'),
            'role_id.exists' => trans('validation.exists'),
        ]);

        return $validator;
    }


}
