<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Storage;

class MigratePhotosAndImages extends Seeder
{
    public function run()
    {
        $addCol = function ($table, $col, $def) {
            if (!Schema::hasColumn($table, $col)) {
                DB::statement("ALTER TABLE {$table} ADD COLUMN {$col} {$def}");
            }
        };

        // 1. Alter ecoles table
        $addCol('ecoles', 'logo_data', "LONGTEXT NULL COMMENT 'Base64 data URI du logo'");
        $addCol('ecoles', 'logo_blob', "LONGBLOB NULL COMMENT 'Image binaire du logo'");
        $addCol('ecoles', 'cachet_data', "LONGTEXT NULL");
        $addCol('ecoles', 'cachet_blob', "LONGBLOB NULL");
        $addCol('ecoles', 'signature_data', "LONGTEXT NULL");
        $addCol('ecoles', 'signature_blob', "LONGBLOB NULL");

        // 2. Alter utilisateurs table
        $addCol('utilisateurs', 'photo_path', "VARCHAR(255) NULL");
        $addCol('utilisateurs', 'photo_data', "LONGTEXT NULL COMMENT 'Base64 data URI'");
        $addCol('utilisateurs', 'photo_blob', "LONGBLOB NULL COMMENT 'Image binaire photo'");

        // 3. Alter etudiants_donnees table
        $addCol('etudiants_donnees', 'photo_path', "VARCHAR(255) NULL");
        $addCol('etudiants_donnees', 'photo_data', "LONGTEXT NULL");

        // 4. Populate ecoles logo & stamps
        $ecoles = DB::table('ecoles')->get();
        foreach ($ecoles as $ecole) {
            $updates = [];
            
            // Logo
            if ($ecole->logo_path && Storage::disk('public')->exists($ecole->logo_path)) {
                $rawLogo = Storage::disk('public')->get($ecole->logo_path);
                $mime = str_ends_with($ecole->logo_path, '.jpg') || str_ends_with($ecole->logo_path, '.jpeg') ? 'image/jpeg' : 'image/png';
                $updates['logo_data'] = 'data:' . $mime . ';base64,' . base64_encode($rawLogo);
                $updates['logo_blob'] = $rawLogo;
            }

            // Cachet
            if ($ecole->cachet_path && Storage::disk('public')->exists($ecole->cachet_path)) {
                $rawCachet = Storage::disk('public')->get($ecole->cachet_path);
                $updates['cachet_data'] = 'data:image/png;base64,' . base64_encode($rawCachet);
                $updates['cachet_blob'] = $rawCachet;
            }

            // Signature
            if ($ecole->signature_path && Storage::disk('public')->exists($ecole->signature_path)) {
                $rawSig = Storage::disk('public')->get($ecole->signature_path);
                $updates['signature_data'] = 'data:image/png;base64,' . base64_encode($rawSig);
                $updates['signature_blob'] = $rawSig;
            }

            if (!empty($updates)) {
                DB::table('ecoles')->where('id', $ecole->id)->update($updates);
            }
        }

        // 5. Populate student photos
        $studentPhotoMap = [
            'E2026-8888' => 'photos/etudiants/mehdi_tazi.jpg',
            'F2026-0001' => 'photos/etudiants/mehdi_tazi.jpg',
            'E2026-9901' => 'photos/etudiants/sara_alami.jpg',
            'P2026-7701' => 'photos/etudiants/yassine_benali.jpg',
        ];

        foreach ($studentPhotoMap as $matricule => $relPath) {
            if (Storage::disk('public')->exists($relPath)) {
                $raw = Storage::disk('public')->get($relPath);
                $base64Data = 'data:image/jpeg;base64,' . base64_encode($raw);

                DB::table('utilisateurs')
                    ->where('matricule', $matricule)
                    ->update([
                        'photo_path' => $relPath,
                        'photo_data' => $base64Data,
                        'photo_blob' => $raw,
                    ]);

                DB::table('etudiants_donnees')
                    ->where('matricule', $matricule)
                    ->update([
                        'photo_path' => $relPath,
                        'photo_data' => $base64Data,
                    ]);
            }
        }
    }
}
