<?php

namespace App\Jobs;

use App\Mail\OtpVerificationMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOtpEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $otp;

    /**
     * Créer une nouvelle instance de job.
     *
     * @param \App\Models\User $user
     * @param string $otp
     * @return void
     */
    public function __construct(User $user, $otp)
    {
        $this->user = $user;
        $this->otp = $otp;
    }

    /**
     * Exécuter le job.
     *
     * @return void
     */
    public function handle()
    {
        // Envoi de l'email avec le code OTP
        Mail::to($this->user->email)->send(new OtpVerificationMail($this->otp));
    }
}
