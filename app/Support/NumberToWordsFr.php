<?php

namespace App\Support;

/**
 * Convertit un nombre entier en toutes lettres, en français.
 * Utilisé pour la mention "Arrêtée la présente facture à la somme de..."
 */
class NumberToWordsFr
{
    protected static array $unites = [
        '', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf',
        'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf',
    ];

    protected static array $dizaines = [
        '', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix',
    ];

    public static function convert(int $nombre): string
    {
        if ($nombre === 0) {
            return 'zéro';
        }

        if ($nombre < 0) {
            return 'moins ' . self::convert(abs($nombre));
        }

        $mots = '';

        if ($nombre >= 1000000000) {
            $milliards = intdiv($nombre, 1000000000);
            $mots .= ($milliards === 1 ? 'un milliard' : self::convertSousMille($milliards) . ' milliards') . ' ';
            $nombre %= 1000000000;
        }

        if ($nombre >= 1000000) {
            $millions = intdiv($nombre, 1000000);
            $mots .= ($millions === 1 ? 'un million' : self::convertSousMille($millions) . ' millions') . ' ';
            $nombre %= 1000000;
        }

        if ($nombre >= 1000) {
            $milliers = intdiv($nombre, 1000);
            $mots .= ($milliers === 1 ? 'mille' : self::convertSousMille($milliers) . ' mille') . ' ';
            $nombre %= 1000;
        }

        if ($nombre > 0) {
            $mots .= self::convertSousMille($nombre);
        }

        return trim(preg_replace('/\s+/', ' ', $mots));
    }

    protected static function convertSousMille(int $nombre): string
    {
        if ($nombre < 20) {
            return self::$unites[$nombre];
        }

        if ($nombre < 100) {
            return self::convertSousCent($nombre);
        }

        $centaines = intdiv($nombre, 100);
        $reste = $nombre % 100;

        $mot = $centaines === 1 ? 'cent' : self::$unites[$centaines] . ' cent';
        if ($reste === 0 && $centaines > 1) {
            $mot .= 's'; // "deux cents" mais "deux cent un"
        }
        if ($reste > 0) {
            $mot .= ' ' . self::convertSousCent($reste);
        }

        return $mot;
    }

    protected static function convertSousCent(int $nombre): string
    {
        if ($nombre < 20) {
            return self::$unites[$nombre];
        }

        $dizaine = intdiv($nombre, 10);
        $unite = $nombre % 10;

        // 70-79 et 90-99 se comptent en base vigésimale ("soixante-dix", "quatre-vingt-dix")
        if ($dizaine === 7 || $dizaine === 9) {
            if ($nombre === 71) {
                return 'soixante et onze';
            }
            $base = $dizaine === 7 ? 'soixante' : 'quatre-vingt';
            $sousNombre = $nombre - ($dizaine === 7 ? 60 : 80);
            return $base . '-' . self::$unites[$sousNombre];
        }

        // 80-89 : "quatre-vingt" sans "et"
        if ($dizaine === 8) {
            return $unite === 0 ? 'quatre-vingts' : 'quatre-vingt-' . self::$unites[$unite];
        }

        $mot = self::$dizaines[$dizaine];
        if ($unite === 0) {
            return $mot;
        }
        if ($unite === 1) {
            return $mot . ' et un';
        }

        return $mot . '-' . self::$unites[$unite];
    }
}
