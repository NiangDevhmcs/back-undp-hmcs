<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OtpVerificationMail extends Mailable
{
    use SerializesModels;

    public $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function build()
    {
        return $this->subject('Code de vérification OTP')
                    ->view('emails.otp_verification')
                    ->with([
                        'otp' => $this->otp,
                    ]);
    }
}
