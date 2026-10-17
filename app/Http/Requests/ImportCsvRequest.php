<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportCsvRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin_ecole';
    }

    /**
     * Règles de validation pour l'import CSV.
     */
    public function rules(): array
    {
        return [
            'fichier_csv' => 'required|file|mimes:csv,txt|max:10240', // 10 MB max
        ];
    }

    /**
     * Messages d'erreur personnalisés.
     */
    public function messages(): array
    {
        return [
            'fichier_csv.required' => 'Veuillez sélectionner un fichier CSV.',
            'fichier_csv.mimes' => 'Le fichier doit être au format CSV (.csv ou .txt).',
            'fichier_csv.max' => 'La taille du fichier ne doit pas dépasser 10 MB.',
        ];
    }
}
