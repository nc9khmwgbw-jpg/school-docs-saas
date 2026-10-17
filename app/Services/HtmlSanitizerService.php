<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlSanitizerService
{
    private static ?HTMLPurifier $purifier = null;

    /**
     * Retourne une instance configurée de HTMLPurifier pour les templates de documents.
     * 
     * Configuration sécurisée permettant uniquement :
     * - Balises de mise en forme : p, br, strong, em, u, h1-h6, ul, ol, li
     * - Tableaux : table, tr, td, th, thead, tbody
     * - Conteneurs : div, span
     * - Images : img (avec attributs src et style uniquement)
     * - Styles inline limités pour la mise en page
     */
    public static function getPurifier(): HTMLPurifier
    {
        if (self::$purifier === null) {
            $config = HTMLPurifier_Config::createDefault();
            
            // Balises autorisées avec attributs class et style pour la mise en page
            $config->set('HTML.Allowed', implode(',', [
                'p[class|style]', 'br', 'strong', 'em', 'u', 'b', 'i',
                'h1[class|style]', 'h2[class|style]', 'h3[class|style]', 'h4[class|style]', 'h5[class|style]', 'h6[class|style]',
                'ul[class|style]', 'ol[class|style]', 'li[class|style]',
                'table[class|style|width|border|cellspacing|cellpadding]', 'thead[class|style]', 'tbody[class|style]', 'tr[class|style]', 'td[class|style|width|colspan|rowspan]', 'th[class|style|width|colspan|rowspan]',
                'div[class|style]', 'span[class|style]', 'hr[class|style]',
                'img[src|alt|style|width|height|class]',
            ]));
            
            // Autoriser les propriétés CSS de mise en page nécessaires pour DomPDF (display, margin, etc.)
            $config->set('CSS.AllowTricky', true);
            
            // Désactiver les liens externes et JavaScript
            $config->set('HTML.Nofollow', true);
            $config->set('URI.DisableExternalResources', false); // Permet les images base64
            $config->set('HTML.TargetBlank', false);
            
            // Encodage UTF-8
            $config->set('Core.Encoding', 'UTF-8');
            
            // Désactiver le cache de sérialisation sur disque ou le pointer vers storage pour éviter les erreurs de permission
            $config->set('Cache.DefinitionImpl', null);
            $purifierCache = storage_path('framework/cache/purifier');
            if (!is_dir($purifierCache)) {
                @mkdir($purifierCache, 0775, true);
            }
            if (is_writable($purifierCache)) {
                $config->set('Cache.SerializerPath', $purifierCache);
            }
            
            self::$purifier = new HTMLPurifier($config);
        }
        
        return self::$purifier;
    }

    /**
     * Nettoie du code HTML pour éliminer les scripts malveillants et le code non autorisé.
     * 
     * @param string $html Code HTML à nettoyer
     * @return string HTML nettoyé et sécurisé
     */
    public static function sanitize(string $html): string
    {
        return self::getPurifier()->purify($html);
    }
}
