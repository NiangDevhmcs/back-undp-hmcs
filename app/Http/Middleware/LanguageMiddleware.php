<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class LanguageMiddleware
{
    public function handle($request, Closure $next)
    {
        // Récupère le header Accept-Language
        $acceptLanguage = $request->header('Accept-Language', config('app.locale'));

        // Extrait le code de langue principal (avant la première virgule ou point-virgule)
        $locale = preg_match('/^([a-zA-Z_-]+)/', $acceptLanguage, $matches)
            ? $matches[1]
            : config('app.locale');

        // Vérifie si le locale extrait est supporté, sinon utilise la locale par défaut
        $supportedLocales = ['fr', 'fr_FR', 'en', 'en_US']; // Ajoutez vos locales supportées
        if (!in_array($locale, $supportedLocales)) {
            $locale = config('app.locale');
        }
        Log::info("-------------->" .$locale);

        App::setLocale($locale);

        return $next($request);
    }
}