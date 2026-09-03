<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class FactureController extends Controller
{
    /**
     * Résout le tenant de la facture, avec repli sur celui du vendeur si
     * l'utilisateur connecté (rare cas de compte mal configuré) n'en a pas.
     */
    protected function resolveTenant(Facture $facture)
    {
        $tenant = auth()->user()->tenant ?? $facture->utilisateur?->tenant;

        if (! $tenant) {
            abort(500, "Impossible de générer le document : aucune boutique (tenant) n'est associée à votre compte. Contactez un administrateur.");
        }

        return $tenant;
    }

    /**
     * Génère le PDF de la facture. Par défaut l'affiche dans le navigateur
     * (?download=1 force le téléchargement direct).
     */
    public function pdf(Request $request, Facture $facture)
    {
        $facture->load(['client', 'items', 'magasin', 'utilisateur', 'paiements']);
        $tenant = $this->resolveTenant($facture);

        $pdf = Pdf::loadView('pdf.facture', [
            'facture' => $facture,
            'tenant' => $tenant,
        ])->setPaper('a4', 'portrait');

        $nomFichier = str_replace(['/', '\\', ' '], '-', $facture->num_facture) . '.pdf';

        return $request->boolean('download')
            ? $pdf->download($nomFichier)
            : $pdf->stream($nomFichier);
    }

    /**
     * Génère le bon de livraison associé à la facture (mêmes lignes, sans les
     * montants — uniquement les quantités livrées, avec cases de signature).
     */
    public function bonLivraison(Request $request, Facture $facture)
    {
        $facture->load(['client', 'items', 'magasin', 'utilisateur']);
        $tenant = $this->resolveTenant($facture);

        $pdf = Pdf::loadView('pdf.bon-livraison', [
            'facture' => $facture,
            'tenant' => $tenant,
        ])->setPaper('a4', 'portrait');

        $nomFichier = 'BL-' . str_replace(['/', '\\', ' '], '-', $facture->num_facture) . '.pdf';

        return $request->boolean('download')
            ? $pdf->download($nomFichier)
            : $pdf->stream($nomFichier);
    }
}
