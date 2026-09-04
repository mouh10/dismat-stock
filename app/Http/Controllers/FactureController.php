<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Support\NumberToWordsFr;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class FactureController extends Controller
{
    /**
     * Résout le tenant de la facture. On privilégie le tenant du vendeur qui a
     * émis la facture (c'est SA boutique qui doit apparaître dessus), avec un
     * repli sur celui de l'utilisateur connecté si besoin.
     */
    protected function resolveTenant(Facture $facture)
    {
        $tenant = $facture->utilisateur?->tenant ?? auth()->user()->tenant;

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

        $montantEnLettres = ucfirst(NumberToWordsFr::convert((int) round((float) $facture->montant_ttc))) . ' francs CFA';

        $pdf = Pdf::loadView('pdf.facture', [
            'facture' => $facture,
            'tenant' => $tenant,
            'montantEnLettres' => $montantEnLettres,
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
