<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class FactureController extends Controller
{
    /**
     * Génère le PDF d'une facture. Par défaut l'affiche dans le navigateur
     * (?download=1 force le téléchargement direct).
     */
    public function pdf(Request $request, Facture $facture)
    {
        $facture->load(['client', 'items', 'magasin', 'utilisateur', 'paiements']);

        // Le tenant vient normalement de l'utilisateur connecté ; si celui-ci n'en a
        // pas (compte mal configuré), on retombe sur le tenant du vendeur de la facture.
        $tenant = auth()->user()->tenant ?? $facture->utilisateur?->tenant;

        if (! $tenant) {
            abort(500, "Impossible de générer le PDF : aucune boutique (tenant) n'est associée à votre compte. Contactez un administrateur.");
        }

        $pdf = Pdf::loadView('pdf.facture', [
            'facture' => $facture,
            'tenant' => $tenant,
        ])->setPaper('a4', 'portrait');

        $nomFichier = str_replace(['/', '\\', ' '], '-', $facture->num_facture) . '.pdf';

        return $request->boolean('download')
            ? $pdf->download($nomFichier)
            : $pdf->stream($nomFichier);
    }
}
