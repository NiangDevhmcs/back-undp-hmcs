<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Responses\ResponseServer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    public function changePassword(ChangePasswordRequest $request, $id)
    {
        $validated = $request->validated();
        $user = User::findOrFail($id);

        // Vérifie si le mot de passe actuel est correct
        if (!Hash::check($validated['current_password'], $user->password)) {
            return ResponseServer::incorrectCurrentPassword();
        }

        // Met à jour le mot de passe
        $user->password = Hash::make($validated['new_password']);
        $user->save();

        app('App\Http\Controllers\AuthController')->logout($request);

        return response()->json([
            'success' => true,
            'message' => trans('message.change_password_success'),
        ], 200);
    }

}
