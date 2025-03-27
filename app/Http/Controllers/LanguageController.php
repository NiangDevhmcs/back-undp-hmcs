<?php

namespace App\Http\Controllers;

use App\Http\Resources\LangueResource;
use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function index(Request $request)
    {
        // Get the selected language from the request or session
        $lang = $request->header('Accept-Language');
        app()->setLocale($lang);


        // Role names to be translated

        $langueKeys = [
            "french",
            "english",
            "portuguese"
        ];

        $languages = Language::all();
        // Translate role names based on the language
        $translatedlanguages = LangueResource::collection($languages);

        // Pass the translated languages to the view
        return response()->json(["data" => $translatedlanguages], 200);
    }

}
