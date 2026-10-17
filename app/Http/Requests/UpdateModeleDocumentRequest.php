<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateModeleDocumentRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin_ecole';
    }

    /**
     * Règles de validation pour la mise à jour d'un modèle de document.
     */
    public function rules(): array
    {
        return [
            'titre'              => 'required|string|max:200',
            'type_document'      => 'required|in:attestation_reussite,certificat_scolarite,releve_notes,convention_stage,certificat,duplicata,autre',
            'description'        => 'nullable|string|max:1000',
            'contenu_html'       => 'required|string',
            'styles_css'         => 'nullable|string|max:50000',
            'validation_requise' => 'boolean',
            'langue'             => 'in:fr,ar,fr_ar',
            'expire_dans_jours'  => 'nullable|integer|min:1|max:3650',
            'actif'              => 'boolean',
        ];
    }

    /**
     * Messages d'erreur personnalisés.
     */
    public function messages(): array
    {
        return [
            'titre.required' => 'Le titre du modèle est obligatoire.',
            'type_document.required' => 'Veuillez sélectionner un type de document.',
            'contenu_html.required' => 'Le contenu HTML du document est obligatoire.',
        ];
    }
}
