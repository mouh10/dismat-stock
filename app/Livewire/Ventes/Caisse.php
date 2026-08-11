<?php

namespace App\Livewire\Ventes;

use App\Exceptions\InsufficientStockException;
use App\Models\Client;
use App\Models\Facture;
use App\Models\FactureItem;
use App\Models\Magasin;
use App\Models\Produit;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Caisse extends Component
{
    public string $search = '';
    public array $cart = []; // [produit_id => ['designation','prix','qte','remise']]
    public ?int $client_id = null;
    public string $clientSearch = '';
    public bool $clientDropdownOpen = false;
    public string $mode_paiement = 'especes';
    public float $montant_recu = 0;

    public ?int $lastFactureId = null;
    public ?int $magasinId = null;

    public function mount()
    {
        $this->magasinId = auth()->user()->magasin_id
            ?? Magasin::where('est_principal', true)->value('id')
            ?? Magasin::value('id');
    }

    /**
     * Stock actuellement disponible pour un produit dans le magasin de la caisse.
     */
    protected function stockDisponible(Produit $produit): float
    {
        if (! $produit->est_stockable) {
            return INF; // pas de suivi de stock => pas de limite
        }

        return $this->magasinId ? $produit->stockDansMagasin($this->magasinId) : 0;
    }

    public function addToCart(int $produitId)
    {
        $produit = Produit::findOrFail($produitId);
        $disponible = $this->stockDisponible($produit);
        $qteActuelle = $this->cart[$produitId]['qte'] ?? 0;

        if ($qteActuelle + 1 > $disponible) {
            session()->flash('error', "Stock insuffisant pour « {$produit->designation} » : {$disponible} disponible(s).");
            return;
        }

        if (isset($this->cart[$produitId])) {
            $this->cart[$produitId]['qte']++;
        } else {
            $this->cart[$produitId] = [
                'designation' => $produit->designation,
                'prix' => (float) $produit->prix_vente,
                'qte' => 1,
                'remise' => 0,
            ];
        }
    }

    public function incrementQte(int $produitId)
    {
        $produit = Produit::find($produitId);
        if (! $produit) {
            return;
        }

        $disponible = $this->stockDisponible($produit);

        if ($this->cart[$produitId]['qte'] + 1 > $disponible) {
            session()->flash('error', "Stock insuffisant pour « {$produit->designation} » : {$disponible} disponible(s).");
            return;
        }

        $this->cart[$produitId]['qte']++;
    }

    public function decrementQte(int $produitId)
    {
        if ($this->cart[$produitId]['qte'] > 1) {
            $this->cart[$produitId]['qte']--;
        } else {
            unset($this->cart[$produitId]);
        }
    }

    public function removeFromCart(int $produitId)
    {
        unset($this->cart[$produitId]);
    }

    public function getTotalProperty(): float
    {
        return collect($this->cart)->sum(fn ($item) => ($item['prix'] * $item['qte']) - $item['remise']);
    }

    public function getMonnaieProperty(): float
    {
        return max(0, $this->montant_recu - $this->total);
    }

    public function openClientDropdown()
    {
        $this->clientDropdownOpen = true;
    }

    public function selectClient(?int $id)
    {
        $this->client_id = $id;

        if ($id) {
            $client = Client::find($id);
            $this->clientSearch = $client ? trim($client->nom . ' ' . $client->prenom) : '';
        } else {
            $this->clientSearch = '';
        }

        $this->clientDropdownOpen = false;
    }

    /**
     * Stock disponible par produit du panier (pour désactiver le "+" à la limite).
     */
    public function getCartDisponibleProperty(): array
    {
        $result = [];
        foreach (array_keys($this->cart) as $produitId) {
            $produit = Produit::find($produitId);
            $result[$produitId] = $produit ? $this->stockDisponible($produit) : INF;
        }
        return $result;
    }

    public function validerVente(StockService $stockService)
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Le panier est vide.');
            return;
        }

        $magasin = $this->magasinId ? Magasin::find($this->magasinId) : null;

        if (! $magasin) {
            session()->flash('error', 'Aucun magasin configuré.');
            return;
        }

        $total = $this->total;
        $factureId = null;

        try {
            DB::transaction(function () use ($magasin, $stockService, $total, &$factureId) {
                $numFacture = 'TK-' . now()->format('ymd') . '-' . str_pad((string) (Facture::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);

                $facture = Facture::create([
                    'client_id' => $this->client_id,
                    'utilisateur_id' => auth()->id(),
                    'magasin_id' => $magasin->id,
                    'type_doc' => 'ticket',
                    'num_facture' => $numFacture,
                    'statut' => 'payee',
                    'montant_ht' => $total,
                    'taux_tva' => 0,
                    'tva' => 0,
                    'montant_remise' => collect($this->cart)->sum('remise'),
                    'montant_ttc' => $total,
                    'montant_paye' => $total,
                    'date_facture' => today(),
                ]);

                $factureId = $facture->id;

                foreach ($this->cart as $produitId => $item) {
                    $produit = Produit::findOrFail($produitId);

                    // Re-vérification côté serveur au moment de la validation :
                    // le stock a pu changer depuis l'ajout au panier (autre caissier, etc.)
                    if ($produit->est_stockable) {
                        $stockService->sortie($produit, $magasin->id, $item['qte'], 'Vente ' . $facture->num_facture, $facture->num_facture);
                    }

                    FactureItem::create([
                        'facture_id' => $facture->id,
                        'product_id' => $produitId,
                        'designation' => $item['designation'],
                        'qte' => $item['qte'],
                        'prix_unitaire' => $item['prix'],
                        'remise' => $item['remise'],
                        'total_ht' => ($item['prix'] * $item['qte']) - $item['remise'],
                    ]);
                }
            });
        } catch (InsufficientStockException $e) {
            session()->flash('error', $e->getMessage() . ' Ajuste le panier et réessaie.');
            return;
        }

        session()->flash('success', 'Vente enregistrée avec succès.');
        $this->lastFactureId = $factureId;
        $this->reset(['cart', 'client_id', 'montant_recu', 'clientSearch', 'clientDropdownOpen']);
        $this->mode_paiement = 'especes';
    }

    public function render()
    {
        $produits = Produit::where('actif', true)
            ->where('magasin_id', $this->magasinId)
            ->when($this->search, fn ($q) => $q->where(function ($q2) {
                $q2->where('designation', 'ilike', "%{$this->search}%")
                   ->orWhere('code_barres', 'ilike', "%{$this->search}%");
            }))
            ->with(['stocks' => fn ($q) => $q->where('magasin_id', $this->magasinId)])
            ->orderBy('designation')
            ->limit(24)
            ->get()
            ->map(function ($p) {
                $p->stock_disponible = $p->est_stockable ? (float) ($p->stocks->first()->quantite ?? 0) : null;
                return $p;
            });

        $clientsFiltres = Client::when($this->clientSearch, fn ($q) => $q->where(function ($q2) {
                $q2->where('nom', 'ilike', "%{$this->clientSearch}%")
                   ->orWhere('prenom', 'ilike', "%{$this->clientSearch}%")
                   ->orWhere('telephone', 'ilike', "%{$this->clientSearch}%");
            }))
            ->orderBy('nom')
            ->limit(8)
            ->get();

        return view('livewire.ventes.caisse', compact('produits', 'clientsFiltres'))
            ->layout('layouts.app', ['title' => 'Caisse']);
    }
}
