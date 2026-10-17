<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfGeneratorService
{
    /**
     * Convertit le HTML en PDF, calcule le hash d'intégrité et sauvegarde le fichier sur le disque.
     *
     * @param string $html Contenu HTML rendu
     * @param int $ecoleId Identifiant de l'école
     * @param string $titreDocument
     * @return array [chemin_fichier, nom_fichier, taille_octets, hash_sha256]
     */
    public function generate(string $html, int $ecoleId, string $titreDocument = 'document'): array
    {
        $pdfOutput = null;

        // 1. Génération avec Dompdf si disponible
        if (class_exists(Dompdf::class)) {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            // ✅ SÉCURITÉ : Désactiver isRemoteEnabled pour neutraliser toute attaque SSRF (métadonnées cloud, scan réseau interne)
            $options->set('isRemoteEnabled', false);
            // ✅ SÉCURITÉ : Chroot strict pour empêcher l'inclusion de fichiers système arbitraires (/etc/passwd, etc.)
            $options->setChroot([
                base_path('public'),
                storage_path('app'),
            ]);
            $options->set('defaultFont', 'DejaVu Serif');
            $options->set('defaultPaperSize', 'a4');
            $options->set('defaultPaperOrientation', 'portrait');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Métadonnées officielles intégrées aux propriétés du PDF
            $canvas = $dompdf->getCanvas();
            if ($canvas && method_exists($canvas, 'get_cpdf')) {
                $cpdf = $canvas->get_cpdf();
                if ($cpdf && method_exists($cpdf, 'addInfo')) {
                    $cpdf->addInfo('Title', $titreDocument);
                    $cpdf->addInfo('Subject', 'Document Scolaire Officiel Certifié');
                    $cpdf->addInfo('Creator', 'DocScolaire SaaS v2.0');
                    $cpdf->addInfo('Producer', 'DocScolaire Anti-Tamper Engine');
                }
            }

            $pdfOutput = $dompdf->output();
        } else {
            // ⚠️ Dompdf est OBLIGATOIRE en production
            throw new \RuntimeException(
                'Dompdf est requis pour générer les PDF. Installez-le avec : composer require dompdf/dompdf'
            );
        }

        // 2. Calcul du hash d'intégrité SHA-256 (Anti-falsification)
        $hashSha256 = hash('sha256', $pdfOutput);

        // 3. Définition du chemin de stockage sécurisé
        $slug = Str::slug($titreDocument);
        $fileUuid = (string) Str::uuid();
        $nomFichier = "{$slug}_{$fileUuid}.pdf";
        $cheminRelatif = "documents/{$ecoleId}/{$nomFichier}";

        // 4. Écriture sur disque privé
        Storage::disk('local')->put($cheminRelatif, $pdfOutput);
        $tailleOctets = strlen($pdfOutput);

        return [
            'chemin_fichier' => $cheminRelatif,
            'nom_fichier'    => $nomFichier,
            'taille_octets'  => $tailleOctets,
            'hash_sha256'    => $hashSha256,
        ];
    }
}
