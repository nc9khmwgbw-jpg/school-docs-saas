<?php

namespace App\Services;

use App\Models\DemandeDocument;
use App\Models\Ecole;
use App\Models\EtudiantDonnee;
use App\Models\ModeleDocument;
use App\Models\Utilisateur;
use Carbon\Carbon;

class TemplateEngineService
{
    /**
     * Fusionne les variables d'un modèle avec les données d'un étudiant et de son école.
     *
     * @param ModeleDocument $modele
     * @param Utilisateur $etudiant
     * @param Ecole $ecole
     * @return string HTML complet prêt pour la génération PDF
     */
    public function render(ModeleDocument $modele, Utilisateur $etudiant, Ecole $ecole, ?string $docUuid = null): string
    {
        // 1. Récupération des données académiques de l'étudiant
        $etudiantData = EtudiantDonnee::where('ecole_id', $ecole->id)
            ->where('matricule', $etudiant->matricule)
            ->first();

        // 2. Préparation du dictionnaire de remplacement par défaut
        $variables = [
            'nom_ecole'           => e($ecole->nom),
            'code_ecole'          => e($ecole->code_unique),
            'adresse_ecole'       => e($ecole->adresse ?? ''),
            'email_ecole'         => e($ecole->email_contact ?? ''),
            'telephone_ecole'     => e($ecole->telephone ?? ''),
            'nom'                 => mb_strtoupper(e($etudiant->nom)),
            'prenom'              => e($etudiant->prenom),
            'matricule'           => e($etudiant->matricule ?? 'N/A'),
            'email_etudiant'      => e($etudiant->email),
            'date_du_jour'        => Carbon::now()->locale('fr')->isoFormat('LL'),
            'annee_actuelle'      => date('Y'),
            'logo'                => !empty($ecole->logo_data)
                ? '<img src="' . $ecole->logo_data . '" style="max-height:85px;max-width:190px;display:block;" alt="Logo ' . e($ecole->nom) . '">'
                : (($ecole->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($ecole->logo_path))
                    ? '<img src="data:image/png;base64,' . base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($ecole->logo_path)) . '" style="max-height:85px;max-width:190px;display:block;" alt="Logo ' . e($ecole->nom) . '">'
                    : '<div style="width:75px;height:75px;border:2px solid #000;border-radius:50%;text-align:center;line-height:71px;font-weight:bold;font-size:15px;letter-spacing:1px;font-family:DejaVu Sans,sans-serif;">' . e($ecole->sigle ?? 'ENS') . '</div>'),
            'cachet'              => !empty($ecole->cachet_data)
                ? '<img src="' . $ecole->cachet_data . '" style="max-height:90px;max-width:140px;display:inline-block;" alt="Sceau Officiel">'
                : (($ecole->cachet_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($ecole->cachet_path))
                    ? '<img src="data:image/png;base64,' . base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($ecole->cachet_path)) . '" style="max-height:90px;max-width:140px;display:inline-block;" alt="Sceau Officiel">'
                    : ''),
            'signature'           => !empty($ecole->signature_data)
                ? '<img src="' . $ecole->signature_data . '" style="max-height:55px;max-width:150px;display:inline-block;" alt="Signature">'
                : (($ecole->signature_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($ecole->signature_path))
                    ? '<img src="data:image/png;base64,' . base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($ecole->signature_path)) . '" style="max-height:55px;max-width:150px;display:inline-block;" alt="Signature">'
                    : ''),
            'photo'               => !empty($etudiant->photo_data)
                ? '<img src="' . $etudiant->photo_data . '" style="width:100px;height:120px;object-fit:cover;border:1px solid #000;display:block;" alt="Photo Étudiant">'
                : (($etudiant->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($etudiant->photo_path))
                    ? '<img src="data:image/jpeg;base64,' . base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($etudiant->photo_path)) . '" style="width:100px;height:120px;object-fit:cover;border:1px solid #000;display:block;" alt="Photo Étudiant">'
                    : ''),
            'photo_etudiant'      => !empty($etudiant->photo_data)
                ? '<img src="' . $etudiant->photo_data . '" style="width:100px;height:120px;object-fit:cover;border:1px solid #000;display:block;" alt="Photo Étudiant">'
                : (($etudiant->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($etudiant->photo_path))
                    ? '<img src="data:image/jpeg;base64,' . base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($etudiant->photo_path)) . '" style="width:100px;height:120px;object-fit:cover;border:1px solid #000;display:block;" alt="Photo Étudiant">'
                    : ''),
        ];

        // 3. Fusion des données issues de l'import (filière, niveau, attributs JSON)
        if ($etudiantData) {
            $variables['filiere'] = e($etudiantData->filiere ?? '');
            $variables['niveau'] = e($etudiantData->niveau ?? '');
            $variables['annee_universitaire'] = e($etudiantData->annee_universitaire ?? '');

            if (is_array($etudiantData->attributs_dynamiques)) {
                foreach ($etudiantData->attributs_dynamiques as $cle => $valeur) {
                    $cleSanitized = strtolower(trim($cle));
                    $variables[$cleSanitized] = is_scalar($valeur) ? e((string)$valeur) : '';
                }
            }
        }

        // 3.5. Extraction et agrégation dynamique des notes réelles (table resultats)
        $resultats = \App\Models\Resultat::where('ecole_id', $ecole->id)
            ->where('etudiant_id', $etudiant->id)
            ->orderBy('semestre')
            ->orderBy('intitule_module')
            ->get();

        $tableData = $this->generateAcademicTable($resultats);

        $variables['tableau_notes']     = $tableData['html'];
        $variables['tableau_resultats'] = $tableData['html'];
        $variables['total_credits']     = $tableData['total_credits'];
        $variables['nombre_modules']    = (string) $tableData['nombre_modules'];
        $variables['decision_jury']     = $tableData['decision_jury'];

        if ($tableData['moyenne'] !== 'N/A') {
            $variables['moyenne_calculee'] = $tableData['moyenne'];
            $variables['mention_calculee'] = $tableData['mention'];
            if (empty($variables['moyenne'])) {
                $variables['moyenne'] = $tableData['moyenne'];
            }
            if (empty($variables['mention'])) {
                $variables['mention'] = $tableData['mention'];
            }
        }

        // 4. Intégration du QR code officiel scannable et vérification numérique en Noir & Blanc
        if ($docUuid) {
            $verifyUrl = url('/verifier/' . $docUuid);

            $qrImgTag = '';
            try {
                if (class_exists(\chillerlan\QRCode\QRCode::class)) {
                    $qrOptions = new \chillerlan\QRCode\QROptions([
                        'outputInterface' => \chillerlan\QRCode\Output\QRGdImagePNG::class,
                        'scale'           => 6,
                    ]);
                    $qrBase64 = (new \chillerlan\QRCode\QRCode($qrOptions))->render($verifyUrl);
                    $qrImgTag = '<img src="' . $qrBase64 . '" style="width:90px;height:90px;display:block;margin:0 auto;" alt="QR Code">';
                }
            } catch (\Throwable $e) {
                $qrImgTag = '<div style="width:70px;height:70px;background:#000000;color:#ffffff;font-size:8px;text-align:center;padding:12px;font-weight:bold;">QR CODE<br/>SCELLÉ</div>';
            }

            $qrBloc = '<table style="margin-top:20px;border:1px solid #000000;background:#ffffff;padding:7px 10px;width:100%;border-collapse:collapse;"><tr>'
                . '<td style="width:98px;vertical-align:middle;text-align:center;padding:2px 8px 2px 2px;border-right:1px solid #999999;">' . $qrImgTag . '</td>'
                . '<td style="padding-left:12px;vertical-align:middle;font-size:9.5px;color:#000000;line-height:1.45;">'
                . '<strong style="color:#000000;font-size:10.5px;text-transform:uppercase;letter-spacing:0.5px;">DOCUMENT ACADÉMIQUE OFFICIEL SCELLÉ NUMÉRIQUEMENT</strong><br/>'
                . '<span style="color:#222222;">Scannez ce QR Code ou accédez à l\'URL suivante pour certifier l\'authenticité de l\'original :</span><br/>'
                . '<span style="color:#000000;font-family:DejaVu Sans Mono, monospace;font-size:9.5px;font-weight:bold;">' . $verifyUrl . '</span><br/>'
                . '<span style="font-family:DejaVu Sans Mono, monospace;font-size:8.5px;color:#333333;">Identifiant Unique : ' . e($docUuid) . '</span><br/>'
                . '<strong style="color:#000000;font-size:8.5px;">[ Sceau Numérique Anti-Fraude &bull; Certifié conforme à l\'original archivé ]</strong>'
                . '</td></tr></table>';

            $variables['qr_code']    = $qrBloc;
            $variables['code_barre'] = $qrBloc;
        } else {
            $variables['qr_code']    = '';
            $variables['code_barre'] = '';
        }

        // 5. ✅ SÉCURITÉ : Sanitization du HTML pour éviter XSS via templates malveillants
        $htmlContenu = HtmlSanitizerService::sanitize($modele->contenu_html);

        // 5.1 Remplacement automatique du tableau des notes pour les relevés de notes
        if ($modele->type_document === 'releve_notes' && !empty($tableData['html'])) {
            if (!str_contains($htmlContenu, '{tableau_notes}') && !str_contains($htmlContenu, '{tableau_resultats}')) {
                // Remplacement du tableau statique d'exemple par le tableau dynamique calculé
                preg_match_all('/<table[^>]*>.*?<\/table>/is', $htmlContenu, $matches);
                foreach ($matches[0] as $tableHtml) {
                    if (str_contains($tableHtml, "Module d'") || (str_contains($tableHtml, 'Module') && str_contains($tableHtml, 'Note'))) {
                        $htmlContenu = str_replace($tableHtml, '{tableau_notes}', $htmlContenu);
                        break;
                    }
                }
            }
        }
        
        // 6. Remplacement des balises {variable} dans le contenu HTML
        foreach ($variables as $cle => $valeur) {
            $pattern = '/\{' . preg_quote($cle, '/') . '\}/i';
            $htmlContenu = preg_replace($pattern, (string)$valeur, $htmlContenu);
        }

        // 7. Nettoyage des balises non résolues pour éviter l'affichage de placeholders
        $htmlContenu = preg_replace('/\{[a-zA-Z0-9_-]+\}/', '—', $htmlContenu);

        // 8. Assemblage du document HTML finalisé avec styles CSS
        $css = HtmlSanitizerService::sanitize($modele->styles_css ?? '');
        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <title>{$modele->titre}</title>
    <style>
        @page {
            margin: 12mm 15mm 12mm 15mm;
            size: A4 portrait;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: "DejaVu Serif", Georgia, "Times New Roman", serif;
            color: #000000;
            background-color: #ffffff;
            font-size: 13px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        table {
            border-collapse: collapse;
        }
        {$css}
    </style>
</head>
<body>
    {$htmlContenu}
</body>
</html>
HTML;
    }

    /**
     * Génère dynamiquement le tableau académique officiel des modules et semestres
     * avec calculs automatiques des moyennes pondérées et validation ECTS.
     */
    protected function generateAcademicTable(\Illuminate\Support\Collection $resultats): array
    {
        if ($resultats->isEmpty()) {
            return [
                'html'             => '',
                'moyenne'          => 'N/A',
                'mention'          => 'N/A',
                'total_credits'    => '0 ECTS',
                'decision_jury'    => 'N/A',
                'nombre_modules'   => 0,
            ];
        }

        $semestres = $resultats->groupBy(function ($item) {
            return !empty($item->semestre) ? trim($item->semestre) : 'Semestre Unique';
        });

        $totalPondereGlobal = 0;
        $totalCoeffGlobal = 0;
        $totalModules = $resultats->count();

        $rowsHtml = '';

        foreach ($semestres as $semestreNom => $modules) {
            $semestrePondere = 0;
            $semestreCoeff = 0;

            // En-tête de semestre
            $rowsHtml .= '<tr style="background:#f4f4f4;font-weight:bold;">'
                . '<td colspan="5" style="padding:6px 8px;border:1px solid #000000;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">'
                . e($semestreNom)
                . '</td></tr>';

            foreach ($modules as $m) {
                $note = (float) $m->note;
                $noteMax = (float) ($m->note_max ?: 20);
                $coeff = (float) ($m->coefficient ?: 1);

                $noteSur20 = $noteMax > 0 ? ($note / $noteMax) * 20 : 0;
                $semestrePondere += $noteSur20 * $coeff;
                $semestreCoeff += $coeff;

                $totalPondereGlobal += $noteSur20 * $coeff;
                $totalCoeffGlobal += $coeff;

                $estValide = $noteSur20 >= 10.0;
                $decision = $estValide ? 'Validé' : 'Non Validé';

                $rowsHtml .= '<tr>'
                    . '<td style="padding:5px 8px;border:1px solid #000000;font-size:10.5px;">' . e($m->intitule_module) . '</td>'
                    . '<td style="padding:5px 8px;text-align:center;border:1px solid #000000;font-size:10.5px;font-weight:bold;">' . number_format($note, 2, '.', '') . ' / ' . number_format($noteMax, 0) . '</td>'
                    . '<td style="padding:5px 8px;text-align:center;border:1px solid #000000;font-size:10.5px;">' . number_format($coeff, 1, '.', '') . ' ECTS</td>'
                    . '<td style="padding:5px 8px;text-align:center;border:1px solid #000000;font-size:10.5px;">' . e($m->session ?: 'Principale') . '</td>'
                    . '<td style="padding:5px 8px;text-align:center;border:1px solid #000000;font-size:10.5px;font-weight:bold;">' . $decision . '</td>'
                    . '</tr>';
            }

            // Sous-total du semestre
            $moyenneSemestre = $semestreCoeff > 0 ? round($semestrePondere / $semestreCoeff, 2) : 0;
            $semestreValide = $moyenneSemestre >= 10.0 ? 'Semestre Validé' : 'Non Validé';

            $rowsHtml .= '<tr style="background:#fafafa;font-weight:bold;border-top:1.5px solid #000000;border-bottom:1.5px solid #000000;">'
                . '<td style="padding:5px 8px;border:1px solid #000000;font-size:10.5px;">Moyenne ' . e($semestreNom) . '</td>'
                . '<td style="padding:5px 8px;text-align:center;border:1px solid #000000;font-size:10.5px;">' . number_format($moyenneSemestre, 2, '.', '') . ' / 20</td>'
                . '<td style="padding:5px 8px;text-align:center;border:1px solid #000000;font-size:10.5px;">' . number_format($semestreCoeff, 1, '.', '') . ' ECTS</td>'
                . '<td colspan="2" style="padding:5px 8px;text-align:center;border:1px solid #000000;font-size:10.5px;">' . $semestreValide . '</td>'
                . '</tr>';
        }

        // Bilan Général Global
        $moyenneGenerale = $totalCoeffGlobal > 0 ? round($totalPondereGlobal / $totalCoeffGlobal, 2) : 0;
        $mention = match (true) {
            $moyenneGenerale >= 16.0 => 'Très Bien',
            $moyenneGenerale >= 14.0 => 'Bien',
            $moyenneGenerale >= 12.0 => 'Assez Bien',
            $moyenneGenerale >= 10.0 => 'Passable',
            default                  => 'Ajourné(e)'
        };
        $decisionJury = $moyenneGenerale >= 10.0 ? 'ADMIS(E)' : 'AJOURNÉ(E)';

        $rowsHtml .= '<tr style="background:#eeeeee;font-weight:bold;border-top:2px solid #000000;border-bottom:2px solid #000000;">'
            . '<td style="padding:7px 8px;border:1px solid #000000;font-size:11px;">BILAN ACADÉMIQUE GLOBAL &bull; Mention : ' . $mention . '</td>'
            . '<td style="padding:7px 8px;text-align:center;border:1px solid #000000;font-size:11px;">' . number_format($moyenneGenerale, 2, '.', '') . ' / 20</td>'
            . '<td style="padding:7px 8px;text-align:center;border:1px solid #000000;font-size:11px;">' . number_format($totalCoeffGlobal, 1, '.', '') . ' ECTS</td>'
            . '<td colspan="2" style="padding:7px 8px;text-align:center;border:1px solid #000000;font-size:11px;letter-spacing:0.5px;">' . $decisionJury . '</td>'
            . '</tr>';

        $fullTable = '<table style="width:100%;border-collapse:collapse;margin:12px 0;font-size:11px;">'
            . '<thead>'
            . '<tr style="background:#e5e5e5;border-top:1.5px solid #000000;border-bottom:1.5px solid #000000;">'
            . '<th style="padding:6px 8px;text-align:left;border:1px solid #000000;">Module d\'Enseignement</th>'
            . '<th style="padding:6px 8px;text-align:center;border:1px solid #000000;width:80px;">Note / 20</th>'
            . '<th style="padding:6px 8px;text-align:center;border:1px solid #000000;width:75px;">Crédits ECTS</th>'
            . '<th style="padding:6px 8px;text-align:center;border:1px solid #000000;width:75px;">Session</th>'
            . '<th style="padding:6px 8px;text-align:center;border:1px solid #000000;width:85px;">Résultat</th>'
            . '</tr>'
            . '</thead>'
            . '<tbody>'
            . $rowsHtml
            . '</tbody>'
            . '</table>';

        return [
            'html'             => $fullTable,
            'moyenne'          => number_format($moyenneGenerale, 2, '.', ''),
            'mention'          => $mention,
            'total_credits'    => number_format($totalCoeffGlobal, 1, '.', '') . ' ECTS',
            'decision_jury'    => $decisionJury,
            'nombre_modules'   => $totalModules,
        ];
    }
}
