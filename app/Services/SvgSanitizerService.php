<?php

namespace App\Services;

use enshrined\svgSanitize\Sanitizer;

class SvgSanitizerService
{
    private static ?Sanitizer $sanitizer = null;

    /**
     * Retourne une instance configurée du Sanitizer SVG.
     */
    public static function getSanitizer(): Sanitizer
    {
        if (self::$sanitizer === null) {
            self::$sanitizer = new Sanitizer();
            self::$sanitizer->minify(true);
            self::$sanitizer->removeRemoteReferences(true);
        }

        return self::$sanitizer;
    }

    /**
     * Nettoie et sécurise le contenu d'un fichier SVG.
     *
     * @param string $svgContent
     * @return string|false Retourne le SVG nettoyé ou false si corrompu/dangereux
     */
    public static function sanitize(string $svgContent): string|false
    {
        $clean = self::getSanitizer()->sanitize($svgContent);
        if (!$clean || empty(trim($clean))) {
            return false;
        }

        return $clean;
    }
}
