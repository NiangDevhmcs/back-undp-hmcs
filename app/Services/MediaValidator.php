<?php

namespace App\Services;

class MediaValidator
{
    public static function validate($file, $maxSizeMB = 1)
    {
        if (!$file->isValid()) {
            return [
                'isValid' => false,
                'message' => 'Le fichier est invalide.'
            ];
        }

        $maxSize = $maxSizeMB * 1024 * 1024; // Conversion en bytes
        if ($file->getSize() > $maxSize) {
            return [
                'isValid' => false,
                'message' => "Le fichier doit faire moins de {$maxSizeMB}MB."
            ];
        }

        $allowedMimes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
            'image/bmp',
            'image/tiff'
        ];

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return [
                'isValid' => false,
                'message' => 'Le type de fichier n\'est pas autorisé.'
            ];
        }

        return [
            'isValid' => true,
            'message' => 'Le fichier est valide.'
        ];
    }
}