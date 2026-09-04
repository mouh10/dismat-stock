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
    public array $cart = [];
    public ?int $client_id = null;
    public string $clientSearch = '';
    public bool $clientDropdownOpen = false;

    // Création rapide d'un nouveau client, sans quitter la Caisse
    public bool $showQuickClientForm = false;
    public string $quickClientNom = '';
    public string $quickClientPrenom = '';
    public string $quickClientTelephone = '';

    public string $mode_paiement = 'especes';
    public float $montant_recu = 0;
    public bool $inclureTva = false;

    public ?int $lastFactureId = null;

    /** Filtre d'affichage du catalogue : '' = tous mes magasins, sinon un ID précis. */
    public string $filterMagasin = '';

    /**
     * Magasin auquel appartient le panier en cours. Se fixe automatiquement au
     * premier produit ajouté (impossible de mélanger deux magasins dans une vente,
     * puisque le stock est un lieu physique).
     */
    public ?int $cartMagasinId = null;

    public bool $multiMagasins = false;

    public function mount()
    {
        $ids = auth()->user()->accessibleMagasinIds();
        $this->multiMagasins = $ids === null || count($ids) > 1;
    }

    /** Les magasins que l'utilisateur a le droit de parcourir/vendre. */
    protected function magasinsAutorises()
    {
        $ids = auth()->user()->accessibleMagasinIds();

        return $ids === null
            ? Magasin::orderBy('nom')->get()
            : Magasin::whereIn('id', $ids)->orderBy('nom')->get();
    }

    protected function magasinIdsAutorises(): ?array
    {
        return auth()->user()->accessibleMagasinIds();
    }

    public function updatedFilterMagasin()
    {
        // Changer le filtre d'affichage ne touche pas au panier en cours : on peut
        // continuer à chercher d'autres produits du même magasin que le panier actif.
    }

    protected function stockDisponible(Produit $produit): float
    {
        if (! $produit->est_stockable) {
            return INF;
        }

        return $produit->stockDansMagasin($produit->magasin_id);
    }

    public function addToCart(int $produitId)
    {
        $produit = Produit::findOrFail($produitId);

        // Un panier ne peut contenir que des produits d'un seul et même magasin
        // (le stock retiré doit venir d'un lieu physique unique).
        if ($this->cartMagasinId !== null && $produit->magasin_id !== $this->cartMagasinId) {
            $magasinPanier = Magasin::find($this->cartMagasinId)?->nom ?? 'un autre magasin';
            session()->flash('error', "Ce produit appartient à un autre magasin. Ton panier contient déjà des articles de « {$magasinPanier} » — vide-le d'abord pour changer de magasin.");
            return;
        }

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
            $this->cartMagasinId = $produit->magasin_id;
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

        if (empty($this->cart)) {
            $this->cartMagasinId = null;
        }
    }

    public function removeFromCart(int $produitId)
    {
        unset($this->cart[$produitId]);

        if (empty($this->cart)) {
            $this->cartMagasinId = null;
        }
    }

    public function viderPanier()
    {
        $this->reset(['cart', 'cartMagasinId']);
    }

    public function getSousTotalProperty(): float
    {
        return collect($this->cart)->sum(fn ($item) => ($item['prix'] * $item['qte']) - $item['remise']);
    }

    public function getTvaMontantProperty(): float
    {
        if (! $this->inclureTva) {
            return 0;
        }
        $taux = (float) (auth()->user()->tenant->tva_defaut ?? 18);

        return $this->sousTotal * ($taux / 100);
    }

    public function getTotalProperty(): float
    {
        return $this->sousTotal + $this->tvaMontant;
    }

    public function getMonnaieProperty(): float
    {
        return max(0, $this->montant_recu - $this->total);
    }

    public function getCartDisponibleProperty(): array
    {
        $result = [];
        foreach (array_keys($this->cart) as $produitId) {
            $produit = Produit::find($produitId);
            $result[$produitId] = $produit ? $this->stockDisponible($produit) : INF;
        }
        return $result;
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

    /** Ouvre le petit formulaire de création rapide, en pré-remplissant le nom déjà tapé. */
    public function openQuickClientForm()
    {
        $this->quickClientNom = $this->clientSearch;
        $this->quickClientPrenom = '';
        $this->quickClientTelephone = '';
        $this->clientDropdownOpen = false;
        $this->showQuickClientForm = true;
    }

    public function cancelQuickClientForm()
    {
        $this->showQuickClientForm = false;
    }

    /** Crée le client à la volée et l'attache immédiatement à la vente en cours. */
    public function saveQuickClient()
    {
        $data = $this->validate([
            'quickClientNom' => 'required|string|max:255',
            'quickClientPrenom' => 'nullable|string|max:255',
            'quickClientTelephone' => 'nullable|string|max:30',
        ], [], [
            'quickClientNom' => 'nom',
            'quickClientPrenom' => 'prénom',
            'quickClientTelephone' => 'téléphone',
        ]);

        $client = Client::create([
            'nom' => $data['quickClientNom'],
            'prenom' => $data['quickClientPrenom'] ?: null,
            'telephone' => $data['quickClientTelephone'] ?: null,
            'type_client' => 'particulier',
        ]);

        $this->selectClient($client->id);
        $this->showQuickClientForm = false;
        session()->flash('success', 'Client « ' . trim($client->nom . ' ' . $client->prenom) . ' » créé et sélectionné.');
    }

    public function validerVente(StockService $stockService)
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Le panier est vide.');
            return;
        }

        $magasin = $this->cartMagasinId ? Magasin::find($this->cartMagasinId) : null;

        if (! $magasin) {
            session()->flash('error', 'Aucun magasin configuré.');
            return;
        }

        $sousTotal = $this->sousTotal;
        $tva = $this->tvaMontant;
        $total = $this->total;
        $tauxTva = $this->inclureTva ? (float) (auth()->user()->tenant->tva_defaut ?? 18) : 0;
        $factureId = null;

        try {
            DB::transaction(function () use ($magasin, $stockService, $sousTotal, $tva, $tauxTva, $total, &$factureId) {
                $numFacture = 'FA-' . now()->format('ymd') . '-' . str_pad((string) (Facture::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);

                $facture = Facture::create([
                    'client_id' => $this->client_id,
                    'utilisateur_id' => auth()->id(),
                    'magasin_id' => $magasin->id,
                    'type_doc' => 'facture',
                    'num_facture' => $numFacture,
                    'statut' => 'payee',
                    'montant_ht' => $sousTotal,
                    'taux_tva' => $tauxTva,
                    'tva' => $tva,
                    'montant_remise' => collect($this->cart)->sum('remise'),
                    'montant_ttc' => $total,
                    'montant_paye' => $total,
                    'date_facture' => today(),
                ]);

                $factureId = $facture->id;

                foreach ($this->cart as $produitId => $item) {
                    $produit = Produit::findOrFail($produitId);

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
        $this->reset(['cart', 'cartMagasinId', 'client_id', 'montant_recu', 'clientSearch', 'clientDropdownOpen', 'inclureTva']);
        $this->mode_paiement = 'especes';
    }

    public function render()
    {
        $ids = $this->magasinIdsAutorises(); // null = admin (tout voir)
        $magasinsDisponibles = $this->magasinsAutorises();

        $produits = Produit::where('actif', true)
            // Une vente déjà commencée reste cantonnée à son magasin ; sinon,
            // on affiche par défaut tous les magasins accessibles à l'utilisateur,
            // ou seulement celui choisi dans le filtre.
            ->when($this->cartMagasinId, fn ($q) => $q->where('magasin_id', $this->cartMagasinId))
            ->when(! $this->cartMagasinId && $this->filterMagasin, fn ($q) => $q->where('magasin_id', $this->filterMagasin))
            ->when(! $this->cartMagasinId && ! $this->filterMagasin && $ids !== null, fn ($q) => $q->whereIn('magasin_id', $ids))
            ->when($this->search, fn ($q) => $q->where(function ($q2) {
                $q2->where('designation', 'ilike', "%{$this->search}%")
                   ->orWhere('code_barres', 'ilike', "%{$this->search}%");
            }))
            ->with(['stocks', 'magasin'])
            ->orderBy('designation')
            ->limit(30)
            ->get()
            ->map(function ($p) {
                $p->stock_disponible = $p->est_stockable ? (float) $p->stockDansMagasin($p->magasin_id) : null;
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

        return view('livewire.ventes.caisse', compact('produits', 'clientsFiltres', 'magasinsDisponibles'))
            ->layout('layouts.app', ['title' => 'Caisse']);
    }
}
