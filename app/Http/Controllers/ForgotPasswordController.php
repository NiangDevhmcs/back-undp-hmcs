<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
{
    try {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Log pour debugging
        Log::info('Tentative de réinitialisation pour: ' . $request->email);

        // Utilisez le broker standard, qui utilise maintenant la notification personnalisée
        // grâce à la méthode sendPasswordResetNotification du modèle User
        $status = Password::broker('users')->sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => trans('message.send_resset_password'),
                'status' => $status
            ]);
        }

        // Log l'erreur spécifique
        Log::error('Erreur reset password: ' . $status);

        // Retourner un message plus spécifique
        $message = match ($status) {
            Password::INVALID_USER => trans('message.email_not_found'),
            Password::RESET_THROTTLED => trans('message.time_attempts'),
            default => trans('message.error_send_resset_password')
        };

        return response()->json(['message' => $message], 400);

    } catch (\Exception $e) {
        Log::info($e);
        Log::error('Exception lors du reset password: ' . $e->getMessage());
        return response()->json([
            'message' => trans('message.error_auth'),
            'error' => $e->getMessage()
        ], 500);
    }
}
}
