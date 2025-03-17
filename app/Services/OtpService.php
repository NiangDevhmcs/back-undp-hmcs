<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class OtpService
{
    protected $otpLength = 6;
    public $otpExpiry = 5; // minutes

    public function generate(User $user)
    {
        // Générer un OTP composé uniquement de chiffres
        $otp = rand(100000, 999999); // Génère un nombre aléatoire à 6 chiffres
        // Sauvegarder l'OTP dans le cache
        Cache::put("otp:{$user->id}", $otp, now()->addMinutes($this->otpExpiry));
        return $otp;
    }
    
    public function verify(User $user, string $otp)
    {
        $cachedOtp = Cache::get("otp:{$user->id}");
        if ($cachedOtp && $cachedOtp == $otp) {
            Cache::forget("otp:{$user->id}");
            return true;
        }
        return false;
    }
}

