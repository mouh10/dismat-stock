<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

/**
 * Centralise toute la logique de mouvement de stock (entrée, sortie, transfert,
 * ajustement) pour garantir que mouvement_stocks et stocks restent cohérents,
 * et qu'un produit ne puisse jamais passer sous zéro dans un magasin.
 */
class StockService
{
    /**
     * @throws InsufficientStockException si l'opération ferait passer le stock sous zéro
     *         (uniquement pour les sorties ; jamais levée pour une entrée).
     */
    public function mouvement(Produit $produit, int $magasinId, string $type, float $quantite, ?string $motif = null, ?string $reference = null): MouvementStock
    {
        return DB::transaction(function () use ($produit, $magasinId, $type, $quantite, $motif, $reference) {
            // Verrou pessimiste : deux ventes simultanées sur le même produit
            // ne peuvent pas lire le même stock "avant" et créer une incohérence.
            $stock = Stock::where('produit_id', $produit->id)
                ->where('magasin_id', $magasinId)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                $stock = Stock::create([
                    'produit_id' => $produit->id,
                    'magasin_id' => $magasinId,
                    'quantite' => 0,
                ]);
            }

            $avant = (float) $stock->quantite;
            $estEntree = in_array($type, ['entree', 'inventaire']) || ($type === 'ajustement' && $quantite > 0);
            $apres = $estEntree ? $avant + abs($quantite) : $avant - abs($quantite);

            if (! $estEntree && $apres < 0 && $produit->est_stockable) {
                throw new InsufficientStockException($produit->designation, $avant, abs($quantite));
            }

            $stock->update(['quantite' => $apres]);

            return MouvementStock::create([
                'produit_id' => $produit->id,
                'magasin_id' => $magasinId,
                'type' => $type,
                'quantite' => abs($quantite),
                'stock_avant' => $avant,
                'stock_apres' => $apres,
                'motif' => $motif,
                'reference' => $reference,
                'utilisateur_id' => auth()->id(),
            ]);
        });
    }

    public function entree(Produit $produit, int $magasinId, float $quantite, ?string $motif = null, ?string $reference = null): MouvementStock
    {
        return $this->mouvement($produit, $magasinId, 'entree', $quantite, $motif, $reference);
    }

    /**
     * @throws InsufficientStockException
     */
    public function sortie(Produit $produit, int $magasinId, float $quantite, ?string $motif = null, ?string $reference = null): MouvementStock
    {
        return $this->mouvement($produit, $magasinId, 'sortie', $quantite, $motif, $reference);
    }

    /**
     * Stock actuellement disponible pour un produit dans un magasin.
     */
    public function disponible(Produit $produit, int $magasinId): float
    {
        return (float) (Stock::where('produit_id', $produit->id)->where('magasin_id', $magasinId)->value('quantite') ?? 0);
    }
}
