<?php

namespace App\Http\Controllers;

use App\Http\Responses\ResponseServer;
use App\Jobs\SendOtpEmailJob;
use App\Models\AttemptConnexion;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\OtpService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // protected const ERROR_MESSAGE = 'Email ou mot de passe incorrect';
    protected const OTP_THRESHOLD = 3;
    protected const INITIAL_BLOCK_TIME = 1;
    protected const REMEMBER_COOKIE_DURATION = 4320; // 3 jours en minutes
    protected const DEFAULT_COOKIE_DURATION = 120; // 2 heures en minutes

    public function logout(Request $request)
    {
        // Supprime les tokens si l'utilisateur existe
        if ($request->user()) {
            $request->user()->tokens()->delete();
        }
        // Force l'expiration de la session
        if ($request->hasSession()) {
            $request->session()->flush();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        // Supprime les cookies - assurez-vous d'inclure tous les cookies d'authentification
        $sessionCookie = cookie()->forget('laravel_session');
        $rememberCookie = cookie()->forget('remember_token');
        $jwtCookie = cookie()->forget('jwt'); // Si vous utilisez JWT

        return response()
            ->json([
                'message' => trans('message.logout_success'),
                'status' => true])
            ->withCookie($sessionCookie)
            ->withCookie($rememberCookie)
            ->withCookie($jwtCookie);
    }

    public function login(Request $request)
    {
        try {
            Log::info($request->all());
            $credentials = ResponseServer::validateLoginData($request);
            if (!is_array($credentials)) {
                return $credentials;
            }

            $remember = isset($credentials['remember_me']) && $credentials['remember_me'] === true;

            $attempt = $this->getLoginAttempt($request);

            if ($this->isBlocked($attempt)) {
                return $this->sendLockoutResponse($attempt);
            }

            $user = User::where('email', $credentials['email'])->first();
            if (!$user->hasVerifiedEmail()) {
                // Renvoyer un email de vérification
                \App\Jobs\SendVerificationEmail::dispatch($user);

                return response()->json([
                    'status' => false,
                    'message' => trans('message.email_verify'),
                    'email_verified' => false
                ], 403);
            }

            if (!$user || !$this->attemptLogin($credentials)) {
                return $this->handleFailedLogin($attempt);
            }

            if ($attempt->attempts >= self::OTP_THRESHOLD) {
                $user->requires_otp = true;
                $user->save();
                return $this->requireOtpVerification($request);
            }

            return $this->handleSuccessfulLogin($request, $remember);

        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage(), [
                'email' => $request->email,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'status' => false,
                'message' => trans('message.incorrect_credential')
            ], 401);
        }
    }

    protected function handleSuccessfulLogin(Request $request, bool $remember = false)
    {
        // Check if session is available before regenerating
        if ($request->hasSession()) {
            $request->session()->regenerate();
            $sessionId = $request->session()->getId();
        } else {
            // Generate a session ID manually if session is not available
            $sessionId = Str::random(40);
        }

        $user = Auth::user();

        $userConnect = User::where('email', $user->email)->first();
        $userConnect->requires_otp = false;
        $userConnect->save();

        // Réinitialisation des tentatives de connexion
        $attempt = AttemptConnexion::where('email', $userConnect->email)
            ->where('ip_address', $request->ip())
            ->first();

        if ($attempt) {
            $this->clearLoginAttempts($attempt);
        }

        $fullName = $user->first_name. ' ' .$user->last_name;

        // Définir la durée du cookie en fonction de l'option remember_me
        $cookieDuration = $remember ? self::REMEMBER_COOKIE_DURATION : self::DEFAULT_COOKIE_DURATION;

        // Configurer le cookie de session
        $sessionCookie = cookie('laravel_session', $sessionId, $cookieDuration);

        // Configurer le cookie de remember_token si remember_me est activé
        $response = response()->json([
            'status' => true,
            'message' => trans('message.login_success', ['name'=>$fullName]),
            'remember_me' => $remember
        ])->withCookie($sessionCookie);

        // Ajouter un cookie remember_token si l'option est activée
        if ($remember) {
            // Générer un nouveau remember_token ou utiliser l'existant
            if (empty($user->remember_token)) {
                $rememberToken = Str::random(60);
                $user->remember_token = $rememberToken;
                $user->save();
            } else {
                $rememberToken = $user->remember_token;
            }

            $rememberCookie = cookie('remember_token', $rememberToken, $cookieDuration);
            $response = $response->withCookie($rememberCookie);
        }

        return $response;
    }

    protected function attemptLogin(array $credentials)
    {
        return Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password']
        ]);
    }

    protected function getLoginAttempt(Request $request)
    {
        return AttemptConnexion::firstOrCreate(
            [
                'ip_address' => $request->ip(),
                'email' => $request->email
            ],
            [
                'attempts' => 0,
                'block_time' => self::INITIAL_BLOCK_TIME,
                'last_attempt_at' => now()
            ]
        );
    }

    protected function isBlocked($attempt): bool
    {
        if (!$attempt->blocked_until) {
            return false;
        }

        return now()->lt($attempt->blocked_until);
    }

    protected function sendLockoutResponse(AttemptConnexion $attempt)
    {
        return response()->json([
            'status' => false,
            'message' => trans('message.attempt_connexion', ['block_time'=>$attempt->block_time]),
        ], 429);
    }

    protected function handleFailedLogin(AttemptConnexion $attempt)
    {
        $attempt->increment('attempts');
        $attempt->last_attempt_at = now();

        // Mise à jour du temps de blocage après 3 échecs
        if ($attempt->attempts > self::OTP_THRESHOLD) {
            // Utilise le block_time existant et le double
            $newBlockTime = $attempt->block_time * 2;
            $attempt->update([
                'block_time' => $newBlockTime,
                'blocked_until' => now()->addMinutes($newBlockTime)
            ]);
        }
        // Première période de blocage après exactement 3 échecs
        elseif ($attempt->attempts === self::OTP_THRESHOLD) {
            $attempt->update([
                'block_time' => self::INITIAL_BLOCK_TIME,
                'blocked_until' => now()->addMinutes(self::INITIAL_BLOCK_TIME)
            ]);
        }

        if ($attempt->attempts >= self::OTP_THRESHOLD) {
            return $this->sendLockoutResponse($attempt);
        }

        throw ValidationException::withMessages([
            'email' => trans('message.incorrect_credential')
        ]);
    }

    protected function requireOtpVerification(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        // Vérifie si l'utilisateur existe
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => [trans('message.no_user_email_found')]
            ]);
        }

        if (!$user->requires_otp) {
            return $this->handleSuccessfulLogin($request);
        }

        try {
            // Générer un code OTP pour l'utilisateur
            $otp = app(OtpService::class)->generate($user);
            $expireIn = app(OtpService::class)->otpExpiry;
            // Dispatche le job pour envoyer l'email en arrière-plan
            SendOtpEmailJob::dispatch($user, $otp);
            return ResponseServer::requireOtpVerification($user, $expireIn);
        } catch (\Exception $e) {
            Log::error('Erreur génération OTP: ' . $e->getMessage());
            throw ValidationException::withMessages([
                'email' => [trans('message.error_sending_otp')]
            ]);
        }
    }

    protected function clearLoginAttempts($attempt): void
    {
        $attempt->update([
            'attempts' => 0,
            'block_time' => self::INITIAL_BLOCK_TIME,
            'blocked_until' => null,
            'last_attempt_at' => null
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string',
            'remember_me' => 'nullable|boolean'
        ]);

        $remember = isset($request->remember_me) && $request->remember_me === true;

        $user = User::where('email', $request->email)->first();

        if (!$user || !app(OtpService::class)->verify($user, $request->otp)) {
            return response()->json([
                'status' => false,
                'message' => trans('message.invalid_otp')
            ], 401);
        }

        Auth::login($user);

        // Réinitialiser les tentatives après validation OTP
        $attempt = AttemptConnexion::where('email', $request->email)
            ->where('ip_address', $request->ip())
            ->first();

        if ($attempt) {
            $this->clearLoginAttempts($attempt);
        }

        return $this->handleSuccessfulLogin($request, $remember);
    }

    public function authenticate(Request $request)
    {
        try {
            // Vérifier si l'utilisateur est déjà authentifié
            $check = Auth::check();

            if ($check) {
                $user = User::with(['role'])->find(Auth::id());
                // $user->profile_photo_path = $user->getFirstMediaUrl('profile_picture');
                return response()->json([
                    'isAuthenticated' => true,
                    'user' => $user,
                ]);
            }

            // Vérifier si un cookie remember_token existe
            $rememberToken = $request->cookie('remember_token');
            if ($rememberToken) {
                $user = User::where('remember_token', $rememberToken)->first();
                if ($user) {
                    Auth::login($user);

                    // Régénérer l'ID de session
                    if ($request->hasSession()) {
                        $request->session()->regenerate();
                    }

                    $user->profile_photo_path = $user->getFirstMediaUrl('profile_picture');
                    return response()->json([
                        'isAuthenticated' => true,
                        'user' => $user,
                    ]);
                }
            }

            return response()->json([
                'isAuthenticated' => false,
                'status_otp' => false
            ]);
        } catch (Exception $e) {
            Log::error('Authenticate error: ' . $e->getMessage());
            return response()->json(["message" => "failed authenticate"], 400);
        }
    }

    public function checkOtp(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'requires_otp' => false
                ]);
            }
            $expireIn = app(OtpService::class)->otpExpiry;

            return ResponseServer::otpVerification($user, $expireIn);

        } catch (Exception $e) {
            Log::error('Check OTP error: ' . $e->getMessage(), [  'email' => $request->email ]);

            return response()->json([
                'status' => false,
                'message' => trans('message.error_auth')
            ], 500);
        }
    }
}
