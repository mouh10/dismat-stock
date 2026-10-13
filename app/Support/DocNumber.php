<?php

namespace App\Support;

use App\Models\Facture;

class DocNumber
{
    /**
     * Prochain numéro du jour pour un préfixe : FA-261009-0001, PF-261009-0001…
     * Se base sur le plus grand numéro déjà émis (et non sur un simple comptage)
     * pour que factures et proformas ne se décalent jamais entre eux.
     */
    public static function next(string $prefix): string
    {
        $debut = $prefix . '-' . now()->format('ymd') . '-';

        $dernier = Facture::where('num_facture', 'like', $debut . '%')
            ->orderByDesc('num_facture')
            ->value('num_facture');

        $suite = $dernier ? ((int) substr($dernier, strlen($debut))) + 1 : 1;

        return $debut . str_pad((string) $suite, 4, '0', STR_PAD_LEFT);
    }
}
