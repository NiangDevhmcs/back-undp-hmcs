<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $locale = Request::header('Accept-Language');

        if ($locale) {
            // Extraire uniquement la première langue
            $locale = explode(',', $locale)[0];

            // Vérifier si la locale est valide
            if (in_array($locale, ['fr', 'fr_FR', 'en', 'en_US'])) {
                App::setLocale($locale);
            } else {
                App::setLocale('fr'); // Définir une valeur par défaut
            }
        }
    }
}
