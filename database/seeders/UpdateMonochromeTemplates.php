<?php

require "/var/www/html/vendor/autoload.php";
$app = require "/var/www/html/bootstrap/app.php";
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$css = <<<CSS
body { font-family: "DejaVu Serif", Georgia, "Times New Roman", serif; color: #000000; margin: 0; padding: 0; }
.cert-container { border: 3px double #000000; padding: 24px 28px; background-color: #ffffff; min-height: 880px; position: relative; }
.header-table { width: 100%; margin-bottom: 10px; border-collapse: collapse; }
.header-left { width: 22%; vertical-align: middle; text-align: left; }
.header-center { width: 56%; vertical-align: middle; text-align: center; }
.header-right { width: 22%; vertical-align: middle; text-align: right; font-size: 10px; color: #000000; }
.republique { font-size: 11px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase; }
.ministere { font-size: 8px; letter-spacing: 0.5px; margin-top: 2px; text-transform: uppercase; }
.school-name { font-size: 14.5px; font-weight: bold; margin-top: 5px; text-transform: uppercase; letter-spacing: 0.8px; }
.dept-name { font-size: 9.5px; font-style: italic; margin-top: 2px; }
.header-divider { border-top: 2px solid #000000; border-bottom: 1px solid #000000; height: 3px; margin: 8px 0 16px 0; }
.title-container { text-align: center; margin-bottom: 18px; }
.doc-title { font-size: 20px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; margin: 0 0 3px 0; color: #000000; }
.doc-ref { font-size: 9.5px; font-family: "DejaVu Sans Mono", monospace; letter-spacing: 0.5px; }
.body-content { font-size: 13px; line-height: 1.65; margin-bottom: 16px; }
.intro-paragraph { margin-bottom: 12px; text-align: justify; }
.student-info-table { width: 92%; margin: 12px auto 16px auto; border-collapse: collapse; }
.student-info-table td { padding: 5px 8px; font-size: 12.5px; vertical-align: top; }
.info-label { width: 38%; font-weight: normal; color: #222222; border-bottom: 1px dotted #888888; }
.info-value { width: 62%; border-bottom: 1px dotted #888888; }
.status-paragraph { margin-bottom: 12px; text-align: justify; text-indent: 20px; }
.closing-paragraph { margin-bottom: 18px; text-align: justify; text-indent: 20px; }
.signatures-table { width: 100%; margin-top: 10px; border-collapse: collapse; }
.sig-col-left { width: 50%; vertical-align: top; text-align: left; font-size: 11px; }
.sig-col-right { width: 50%; vertical-align: top; text-align: center; font-size: 11px; }
.sig-role { font-weight: bold; margin-bottom: 3px; }
.sig-sub { font-size: 10px; font-style: italic; }
.sig-stamp-box { margin-top: 4px; height: 85px; position: relative; }
.anti-fraud-footer { margin-top: 12px; }
CSS;

$scolariteHtml = <<<HTML
<div class="cert-container">
    <table class="header-table">
        <tr>
            <td class="header-left">{logo}</td>
            <td class="header-center">
                <div class="republique">ROYAUME DU MAROC</div>
                <div class="ministere">MINISTÈRE DE L'ENSEIGNEMENT SUPÉRIEUR ET DE LA RECHERCHE SCIENTIFIQUE</div>
                <div class="school-name">{nom_ecole}</div>
                <div class="dept-name">Direction des Études et des Affaires Académiques</div>
            </td>
            <td class="header-right">
                <div class="academic-year">Année Universitaire<br/><strong>{annee_universitaire}</strong></div>
            </td>
        </tr>
    </table>

    <div class="header-divider"></div>

    <div class="title-container">
        <h1 class="doc-title">CERTIFICAT DE SCOLARITÉ</h1>
        <div class="doc-ref">Réf : SCOL/{annee_actuelle}/{matricule}</div>
    </div>

    <div class="body-content">
        <p class="intro-paragraph">
            Le Directeur de l'établissement <strong>{nom_ecole}</strong>, certifie par la présente que :
        </p>

        <table class="student-info-table">
            <tr>
                <td class="info-label">Nom et Prénom :</td>
                <td class="info-value"><strong>{nom} {prenom}</strong></td>
            </tr>
            <tr>
                <td class="info-label">Matricule académique :</td>
                <td class="info-value"><strong>{matricule}</strong></td>
            </tr>
            <tr>
                <td class="info-label">Filière d'études :</td>
                <td class="info-value"><strong>{filiere}</strong></td>
            </tr>
            <tr>
                <td class="info-label">Cycle &amp; Niveau :</td>
                <td class="info-value"><strong>{niveau}</strong></td>
            </tr>
            <tr>
                <td class="info-label">Année universitaire :</td>
                <td class="info-value"><strong>{annee_universitaire}</strong></td>
            </tr>
        </table>

        <p class="status-paragraph">
            Est régulièrement inscrit(e) au sein de notre établissement pour l'année universitaire en cours et poursuit son cursus conformément aux dispositions et règlements pédagogiques en vigueur.
        </p>

        <p class="closing-paragraph">
            Le présent certificat lui est délivré sur sa demande pour servir et valoir ce que de droit auprès des administrations et organismes compétents.
        </p>
    </div>

    <table class="signatures-table">
        <tr>
            <td class="sig-col-left">
                <div class="issue-date">Fait à {adresse_ecole}, le {date_du_jour}</div>
                <div style="margin-top:10px;">
                    <div class="sig-role">Pour la Direction des Études</div>
                    <div class="sig-sub">Le Secrétariat Académique</div>
                </div>
            </td>
            <td class="sig-col-right">
                <div class="sig-role">Le Directeur de l'Établissement</div>
                <table style="width:100%;margin-top:6px;border-collapse:collapse;">
                    <tr>
                        <td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{signature}</td>
                        <td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{cachet}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="anti-fraud-footer">
        {qr_code}
    </div>
</div>
HTML;

$reussiteHtml = <<<HTML
<div class="cert-container">
    <table class="header-table">
        <tr>
            <td class="header-left">{logo}</td>
            <td class="header-center">
                <div class="republique">ROYAUME DU MAROC</div>
                <div class="ministere">MINISTÈRE DE L'ENSEIGNEMENT SUPÉRIEUR ET DE LA RECHERCHE SCIENTIFIQUE</div>
                <div class="school-name">{nom_ecole}</div>
                <div class="dept-name">Jury Académique d'Évaluation &amp; Délibération</div>
            </td>
            <td class="header-right">
                <div class="academic-year">Session Pédagogique<br/><strong>Juin {annee_actuelle}</strong></div>
            </td>
        </tr>
    </table>

    <div class="header-divider"></div>

    <div class="title-container">
        <h1 class="doc-title">ATTESTATION DE RÉUSSITE</h1>
        <div class="doc-ref">Réf : REUSS/{annee_actuelle}/{matricule}</div>
    </div>

    <div class="body-content">
        <p class="intro-paragraph">
            Le Président du Jury et Directeur de l'établissement <strong>{nom_ecole}</strong>, atteste par la présente que :
        </p>

        <table class="student-info-table">
            <tr>
                <td class="info-label">Nom et Prénom :</td>
                <td class="info-value"><strong>{nom} {prenom}</strong></td>
            </tr>
            <tr>
                <td class="info-label">Matricule académique :</td>
                <td class="info-value"><strong>{matricule}</strong></td>
            </tr>
            <tr>
                <td class="info-label">Filière d'études :</td>
                <td class="info-value"><strong>{filiere}</strong></td>
            </tr>
            <tr>
                <td class="info-label">Cycle &amp; Niveau :</td>
                <td class="info-value"><strong>{niveau}</strong></td>
            </tr>
            <tr>
                <td class="info-label">Année universitaire :</td>
                <td class="info-value"><strong>{annee_universitaire}</strong></td>
            </tr>
            <tr>
                <td class="info-label">Résultat &amp; Mention :</td>
                <td class="info-value"><strong>ADMIS(E) — Mention {mention}</strong> (Moyenne : <strong>{moyenne}/20</strong>)</td>
            </tr>
        </table>

        <p class="status-paragraph">
            A satisfait aux examens de fin d'année et aux exigences pédagogiques requises pour la validation intégrale des modules et des crédits de la filière.
        </p>

        <p class="closing-paragraph">
            La présente attestation lui est délivrée pour servir et valoir ce que de droit.
        </p>
    </div>

    <table class="signatures-table">
        <tr>
            <td class="sig-col-left">
                <div class="issue-date">Fait à {adresse_ecole}, le {date_du_jour}</div>
                <div class="sig-role">Le Président du Jury</div>
            </td>
            <td class="sig-col-right">
                <div class="sig-role">Le Directeur de l'Établissement</div>
                <table style="width:100%;margin-top:6px;border-collapse:collapse;">
                    <tr>
                        <td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{signature}</td>
                        <td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{cachet}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="anti-fraud-footer">
        {qr_code}
    </div>
</div>
HTML;

$inscriptionHtml = <<<HTML
<div class="cert-container">
    <table class="header-table">
        <tr>
            <td class="header-left">{logo}</td>
            <td class="header-center">
                <div class="republique">ROYAUME DU MAROC</div>
                <div class="ministere">MINISTÈRE DE L'ENSEIGNEMENT SUPÉRIEUR ET DE LA RECHERCHE SCIENTIFIQUE</div>
                <div class="school-name">{nom_ecole}</div>
                <div class="dept-name">Bureau des Admissions &amp; Inscriptions</div>
            </td>
            <td class="header-right">
                <div class="academic-year">Inscription<br/><strong>{annee_universitaire}</strong></div>
            </td>
        </tr>
    </table>

    <div class="header-divider"></div>

    <div class="title-container">
        <h1 class="doc-title">ATTESTATION D'INSCRIPTION</h1>
        <div class="doc-ref">Réf : INSC/{annee_actuelle}/{matricule}</div>
    </div>

    <div class="body-content">
        <p class="intro-paragraph">
            Le Secrétariat Général et la Direction de <strong>{nom_ecole}</strong> attestent par la présente que :
        </p>

        <table class="student-info-table">
            <tr>
                <td class="info-label">Nom et Prénom :</td>
                <td class="info-value"><strong>{nom} {prenom}</strong></td>
            </tr>
            <tr>
                <td class="info-label">Matricule académique :</td>
                <td class="info-value"><strong>{matricule}</strong></td>
            </tr>
            <tr>
                <td class="info-label">Filière d'études :</td>
                <td class="info-value"><strong>{filiere}</strong></td>
            </tr>
            <tr>
                <td class="info-label">Cycle &amp; Niveau :</td>
                <td class="info-value"><strong>{niveau}</strong></td>
            </tr>
            <tr>
                <td class="info-label">Année universitaire :</td>
                <td class="info-value"><strong>{annee_universitaire}</strong></td>
            </tr>
        </table>

        <p class="status-paragraph">
            L'étudiant(e) susmentionné(e) a accompli l'ensemble des formalités administratives et académiques requises et est formellement inscrit(e) sur les registres officiels de l'établissement pour l'année universitaire <strong>{annee_universitaire}</strong>.
        </p>

        <p class="closing-paragraph">
            Cette attestation est délivrée pour faire valoir auprès des organismes et administrations concernés.
        </p>
    </div>

    <table class="signatures-table">
        <tr>
            <td class="sig-col-left">
                <div class="issue-date">Fait à {adresse_ecole}, le {date_du_jour}</div>
                <div class="sig-role">Le Service des Admissions</div>
            </td>
            <td class="sig-col-right">
                <div class="sig-role">Le Directeur de l'Établissement</div>
                <table style="width:100%;margin-top:6px;border-collapse:collapse;">
                    <tr>
                        <td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{signature}</td>
                        <td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{cachet}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="anti-fraud-footer">
        {qr_code}
    </div>
</div>
HTML;

$releveHtml = <<<HTML
<div class="cert-container">
    <table class="header-table">
        <tr>
            <td class="header-left">{logo}</td>
            <td class="header-center">
                <div class="republique">ROYAUME DU MAROC</div>
                <div class="ministere">MINISTÈRE DE L'ENSEIGNEMENT SUPÉRIEUR ET DE LA RECHERCHE SCIENTIFIQUE</div>
                <div class="school-name">{nom_ecole}</div>
                <div class="dept-name">Registre Central des Notes &amp; Crédits ECTS</div>
            </td>
            <td class="header-right">
                <div class="academic-year">Session Pédagogique<br/><strong>{annee_universitaire}</strong></div>
            </td>
        </tr>
    </table>

    <div class="header-divider"></div>

    <div class="title-container">
        <h1 class="doc-title">RELEVÉ DE NOTES OFFICIEL</h1>
        <div class="doc-ref">Réf : TRANS/{annee_actuelle}/{matricule}</div>
    </div>

    <div class="body-content">
        <table class="student-info-table">
            <tr>
                <td class="info-label">Nom et Prénom :</td>
                <td class="info-value"><strong>{nom} {prenom}</strong></td>
            </tr>
            <tr>
                <td class="info-label">Matricule académique :</td>
                <td class="info-value"><strong>{matricule}</strong></td>
            </tr>
            <tr>
                <td class="info-label">Filière d'études :</td>
                <td class="info-value"><strong>{filiere}</strong> ({niveau})</td>
            </tr>
            <tr>
                <td class="info-label">Année universitaire :</td>
                <td class="info-value"><strong>{annee_universitaire}</strong></td>
            </tr>
        </table>

        <table style="width:100%;border-collapse:collapse;margin:10px 0;font-size:11px;">
            <thead>
                <tr style="background:#f0f0f0;border-top:1.5px solid #000;border-bottom:1.5px solid #000;">
                    <th style="padding:5px;text-align:left;border:1px solid #000;">Module d'Enseignement</th>
                    <th style="padding:5px;text-align:center;border:1px solid #000;width:75px;">Note / 20</th>
                    <th style="padding:5px;text-align:center;border:1px solid #000;width:75px;">Crédits ECTS</th>
                    <th style="padding:5px;text-align:center;border:1px solid #000;width:85px;">Résultat</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding:5px;border:1px solid #000;">Génie Logiciel Avancé &amp; Architecture Système</td>
                    <td style="padding:5px;text-align:center;border:1px solid #000;font-weight:bold;">18.00</td>
                    <td style="padding:5px;text-align:center;border:1px solid #000;">12</td>
                    <td style="padding:5px;text-align:center;border:1px solid #000;">Validé</td>
                </tr>
                <tr>
                    <td style="padding:5px;border:1px solid #000;">Sécurité Informatique &amp; Cryptographie Appliquée</td>
                    <td style="padding:5px;text-align:center;border:1px solid #000;font-weight:bold;">17.50</td>
                    <td style="padding:5px;text-align:center;border:1px solid #000;">10</td>
                    <td style="padding:5px;text-align:center;border:1px solid #000;">Validé</td>
                </tr>
                <tr>
                    <td style="padding:5px;border:1px solid #000;">Bases de Données Distribuées &amp; Cloud Computing</td>
                    <td style="padding:5px;text-align:center;border:1px solid #000;font-weight:bold;">16.50</td>
                    <td style="padding:5px;text-align:center;border:1px solid #000;">10</td>
                    <td style="padding:5px;text-align:center;border:1px solid #000;">Validé</td>
                </tr>
                <tr>
                    <td style="padding:5px;border:1px solid #000;">Management de Projets &amp; Méthodes Agiles</td>
                    <td style="padding:5px;text-align:center;border:1px solid #000;font-weight:bold;">17.00</td>
                    <td style="padding:5px;text-align:center;border:1px solid #000;">8</td>
                    <td style="padding:5px;text-align:center;border:1px solid #000;">Validé</td>
                </tr>
                <tr style="background:#f0f0f0;border-top:1.5px solid #000;border-bottom:1.5px solid #000;font-weight:bold;">
                    <td style="padding:6px;border:1px solid #000;">Moyenne Générale &bull; Mention : {mention}</td>
                    <td style="padding:6px;text-align:center;border:1px solid #000;font-size:11.5px;">{moyenne} / 20</td>
                    <td style="padding:6px;text-align:center;border:1px solid #000;">40 ECTS</td>
                    <td style="padding:6px;text-align:center;border:1px solid #000;">ADMIS(E)</td>
                </tr>
            </tbody>
        </table>
    </div>

    <table class="signatures-table">
        <tr>
            <td class="sig-col-left">
                <div class="issue-date">Fait à {adresse_ecole}, le {date_du_jour}</div>
                <div class="sig-role">Le Responsable des Délibérations</div>
            </td>
            <td class="sig-col-right">
                <div class="sig-role">Le Directeur de l'Établissement</div>
                <table style="width:100%;margin-top:6px;border-collapse:collapse;">
                    <tr>
                        <td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{signature}</td>
                        <td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{cachet}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="anti-fraud-footer">
        {qr_code}
    </div>
</div>
HTML;

$stageHtml = <<<HTML
<div class="cert-container">
    <table class="header-table">
        <tr>
            <td class="header-left">{logo}</td>
            <td class="header-center">
                <div class="republique">ROYAUME DU MAROC</div>
                <div class="ministere">MINISTÈRE DE L'ENSEIGNEMENT SUPÉRIEUR ET DE LA RECHERCHE SCIENTIFIQUE</div>
                <div class="school-name">{nom_ecole}</div>
                <div class="dept-name">Direction des Relations Entreprises &amp; Insertion Professionnelle</div>
            </td>
            <td class="header-right">
                <div class="academic-year">Convention<br/><strong>{annee_universitaire}</strong></div>
            </td>
        </tr>
    </table>

    <div class="header-divider"></div>

    <div class="title-container">
        <h1 class="doc-title">CONVENTION &amp; ATTESTATION DE STAGE</h1>
        <div class="doc-ref">Réf : STAGE/{annee_actuelle}/{matricule}</div>
    </div>

    <div class="body-content">
        <p class="intro-paragraph">
            La Direction de l'établissement <strong>{nom_ecole}</strong> certifie par la présente que :
        </p>

        <table class="student-info-table">
            <tr>
                <td class="info-label">Nom et Prénom :</td>
                <td class="info-value"><strong>{nom} {prenom}</strong></td>
            </tr>
            <tr>
                <td class="info-label">Matricule académique :</td>
                <td class="info-value"><strong>{matricule}</strong></td>
            </tr>
            <tr>
                <td class="info-label">Filière d'études :</td>
                <td class="info-value"><strong>{filiere}</strong> ({niveau})</td>
            </tr>
            <tr>
                <td class="info-label">Année universitaire :</td>
                <td class="info-value"><strong>{annee_universitaire}</strong></td>
            </tr>
        </table>

        <p class="status-paragraph">
            Est autorisé(e) à effectuer son stage d'immersion professionnelle ou stage de fin d'études en entreprise, conformément au cursus pédagogique prescrit. L'étudiant(e) demeure sous statut d'assurance scolaire et académique pendant toute la durée conventionnelle du stage.
        </p>

        <p class="closing-paragraph">
            La présente convention et attestation est délivrée pour valoir ce que de droit auprès de l'organisme d'accueil.
        </p>
    </div>

    <table class="signatures-table">
        <tr>
            <td class="sig-col-left">
                <div class="issue-date">Fait à {adresse_ecole}, le {date_du_jour}</div>
                <div class="sig-role">Le Responsable des Stages</div>
            </td>
            <td class="sig-col-right">
                <div class="sig-role">Le Directeur de l'Établissement</div>
                <table style="width:100%;margin-top:6px;border-collapse:collapse;">
                    <tr>
                        <td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{signature}</td>
                        <td style="text-align:center;vertical-align:middle;width:50%;padding:0;">{cachet}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="anti-fraud-footer">
        {qr_code}
    </div>
</div>
HTML;

$updates = [
    // ENS (ecole_id = 1)
    1  => [$reussiteHtml, $css, 'Attestation de Réussite'],
    2  => [$scolariteHtml, $css, 'Certificat de Scolarité'],
    10 => [$inscriptionHtml, $css, 'Attestation d\'Inscription'],
    11 => [$releveHtml, $css, 'Relevé de Notes Officiel'],

    // POLY (ecole_id = 2)
    3  => [$inscriptionHtml, $css, 'Attestation d\'Inscription'],
    6  => [$scolariteHtml, $css, 'Certificat de Scolarité'],
    7  => [$reussiteHtml, $css, 'Attestation de Réussite'],
    8  => [$releveHtml, $css, 'Relevé de Notes Officiel'],
    9  => [$stageHtml, $css, 'Convention & Attestation de Stage'],

    // FST (ecole_id = 3)
    4  => [$reussiteHtml, $css, 'Attestation de Réussite'],
    5  => [$scolariteHtml, $css, 'Certificat de Scolarité'],
    12 => [$inscriptionHtml, $css, 'Attestation d\'Inscription'],
    13 => [$releveHtml, $css, 'Relevé de Notes Officiel'],
];

foreach ($updates as $id => $data) {
    \App\Models\ModeleDocument::withoutGlobalScopes()->where("id", $id)->update([
        "contenu_html" => $data[0],
        "styles_css"   => $data[1],
        "titre"        => $data[2],
    ]);
}

echo "SUCCESSFULLY UPDATED ALL 13 DOCUMENT MODELS\n";
