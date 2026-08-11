<?php

namespace App\Exceptions;

use Exception;

/**
 * Levée quand une opération (vente, sortie manuelle...) demanderait de faire
 * passer le stock d'un produit sous zéro dans un magasin donné.
 */
class InsufficientStockException extends Exception
{
    public function __construct(string $designation, float $disponible, float $demande)
    {
        $dispo = rtrim(rtrim(number_format($disponible, 2, ',', ' '), '0'), ',');
        $dem = rtrim(rtrim(number_format($demande, 2, ',', ' '), '0'), ',');

        parent::__construct("Stock insuffisant pour « {$designation} » : {$dispo} disponible(s), {$dem} demandé(s).");
    }
}
