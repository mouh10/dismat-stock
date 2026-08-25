<?php

namespace App\Livewire\Produits;

use App\Models\Category;
use App\Models\Magasin;
use App\Models\Produit;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'search')]
    public string $search = '';
    #[Url(as: 'categorie')]
    public string $filterCategory = '';
    #[Url(as: 'magasin')]
    public string $filterMagasin = '';
    #[Url(as: 'statut')]
    public string $filterStatut = 'actifs';
    #[Url(as: 'stock')]
    public string $filterStock = '';
    public bool $showModal = false;
    public ?int $editingId = null;
    public ?int $confirmingDeleteId = null;
    public string $confirmMessage = 'Ce produit et son historique de stock seront définitivement supprimés.';

    public string $designation = '';
    public string $sku = '';
    public ?int $category_id = null;
    public ?int $magasin_id = null;
    public string $marque = '';
    public string $description = '';
    public float $prix_achat = 0;
    public float $prix_vente = 0;
    public ?float $prix_vente_gros = null;
    public int $stock_min = 0;
    public string $code_barres = '';
    public string $unite = 'pc';
    public string $type_produit = 'neuf';
    public bool $actif = true;
    public bool $est_stockable = true;
    public float $stock_initial = 0;

    public array $unites = [
        'pc' => 'Pièce (pc)',
        'kg' => 'Kilogramme (kg)',
        'L' => 'Litre (L)',
        'm' => 'Mètre (m)',
        'carton' => 'Carton',
        'paquet' => 'Paquet',
        'boite' => 'Boîte',
        'g' => 'Gramme (g)',
    ];

    public array $typesProduit = [
        'neuf' => 'Standard (Neuf)',
        'occasion' => 'Occasion',
        'reconditionne' => 'Reconditionné',
    ];

    protected function rules(): array
    {
        return [
            'designation' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'magasin_id' => 'required|exists:magasins,id',
            'marque' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'prix_achat' => 'required|numeric|min:0',
            'prix_vente' => 'required|numeric|min:0',
            'prix_vente_gros' => 'nullable|numeric|min:0',
            'stock_min' => 'required|integer|min:0',
            'code_barres' => 'nullable|string|max:100',
            'unite' => 'nullable|string|max:50',
            'type_produit' => 'nullable|string|max:50',
            'actif' => 'boolean',
            'est_stockable' => 'boolean',
            'stock_initial' => 'nullable|numeric|min:0',
        ];
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterCategory() { $this->resetPage(); }
    public function updatingFilterMagasin() { $this->resetPage(); }
    public function updatingFilterStatut() { $this->resetPage(); }
    public function updatingFilterStock() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'filterCategory', 'filterMagasin', 'filterStock']);
        $this->filterStatut = 'actifs';
        $this->resetPage();
    }

    /** Le ou les magasins auxquels cet utilisateur peut assigner un produit. */
    protected function magasinsAutorises()
    {
        $ids = auth()->user()->accessibleMagasinIds();

        return $ids === null
            ? Magasin::orderBy('nom')->get()
            : Magasin::whereIn('id', $ids)->orderBy('nom')->get();
    }

    public function create()
    {
        $this->reset([
            'designation', 'sku', 'category_id', 'marque', 'description', 'prix_achat', 'prix_vente',
            'prix_vente_gros', 'stock_min', 'code_barres', 'stock_initial', 'editingId',
        ]);
        $this->unite = 'pc';
        $this->type_produit = 'neuf';
        $this->actif = true;
        $this->est_stockable = true;

        $ids = auth()->user()->accessibleMagasinIds();
        // Si l'utilisateur n'a accès qu'à un seul magasin, on le pré-sélectionne.
        $this->magasin_id = ($ids !== null && count($ids) === 1) ? $ids[0] : null;

        $this->showModal = true;
    }

    public function generateSku()
    {
        $this->sku = 'SKU-' . strtoupper(substr(bin2hex(random_bytes(5)), 0, 8));
    }

    protected function peutAccederAuMagasin(?int $magasinId): bool
    {
        $ids = auth()->user()->accessibleMagasinIds();

        return $ids === null || in_array($magasinId, $ids, true);
    }

    public function edit(int $id)
    {
        $p = Produit::findOrFail($id);

        if (! $this->peutAccederAuMagasin($p->magasin_id)) {
            session()->flash('error', "Ce produit appartient à un magasin auquel vous n'avez pas accès.");
            return;
        }

        $this->editingId = $p->id;
        $this->designation = $p->designation;
        $this->sku = (string) $p->sku;
        $this->category_id = $p->category_id;
        $this->magasin_id = $p->magasin_id;
        $this->marque = (string) $p->marque;
        $this->description = (string) $p->description;
        $this->prix_achat = (float) $p->prix_achat;
        $this->prix_vente = (float) $p->prix_vente;
        $this->prix_vente_gros = $p->prix_vente_gros ? (float) $p->prix_vente_gros : null;
        $this->stock_min = $p->stock_min;
        $this->code_barres = (string) $p->code_barres;
        $this->unite = $p->unite ?: 'pc';
        $this->type_produit = $p->type_produit ?: 'neuf';
        $this->actif = (bool) $p->actif;
        $this->est_stockable = (bool) $p->est_stockable;
        $this->stock_initial = 0;
        $this->showModal = true;
    }

    public function save()
    {
        // Impossible d'assigner un produit à un magasin hors de sa liste autorisée,
        // même en manipulant la requête.
        if (! $this->peutAccederAuMagasin($this->magasin_id)) {
            session()->flash('error', "Vous n'avez pas accès à ce magasin.");
            $this->showModal = false;
            return;
        }

        if ($this->editingId) {
            $cible = Produit::find($this->editingId);
            if (! $cible || ! $this->peutAccederAuMagasin($cible->magasin_id)) {
                session()->flash('error', 'Action non autorisée.');
                $this->showModal = false;
                return;
            }
        }

        $data = $this->validate();
        unset($data['stock_initial']);

        DB::transaction(function () use ($data) {
            if ($this->editingId) {
                Produit::findOrFail($this->editingId)->update($data);
                session()->flash('success', 'Produit mis à jour.');
            } else {
                $produit = Produit::create($data);

                if ($this->stock_initial > 0) {
                    Stock::create([
                        'produit_id' => $produit->id,
                        'magasin_id' => $data['magasin_id'],
                        'quantite' => $this->stock_initial,
                    ]);
                }
                session()->flash('success', 'Produit créé.');
            }
        });

        $this->showModal = false;
        $this->reset(['designation', 'sku', 'category_id', 'marque', 'description', 'prix_achat', 'prix_vente', 'stock_initial', 'editingId']);
    }

    public function confirmDelete(int $id)
    {
        $p = Produit::findOrFail($id);
        if (! $this->peutAccederAuMagasin($p->magasin_id)) {
            session()->flash('error', "Ce produit appartient à un magasin auquel vous n'avez pas accès.");
            return;
        }
        $this->confirmingDeleteId = $id;
    }

    public function cancelDelete()
    {
        $this->confirmingDeleteId = null;
    }

    public function delete()
    {
        if (! $this->confirmingDeleteId) {
            return;
        }
        Produit::findOrFail($this->confirmingDeleteId)->delete();
        session()->flash('success', 'Produit supprimé.');
        $this->confirmingDeleteId = null;
    }

    public function render()
    {
        $ids = auth()->user()->accessibleMagasinIds(); // null = admin (tout voir)

        $produits = Produit::with(['category', 'magasin'])
            ->withSum('stocks as stock_total', 'quantite')
            ->when($ids !== null, fn ($q) => $q->whereIn('magasin_id', $ids))
            ->when($ids === null && $this->filterMagasin, fn ($q) => $q->where('magasin_id', $this->filterMagasin))
            ->when($ids !== null && $this->filterMagasin, fn ($q) => $q->where('magasin_id', $this->filterMagasin))
            ->when($this->search, fn ($q) => $q->where(function ($q2) {
                $q2->where('designation', 'ilike', "%{$this->search}%")
                   ->orWhere('code_barres', 'ilike', "%{$this->search}%")
                   ->orWhere('sku', 'ilike', "%{$this->search}%");
            }))
            ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->filterStatut === 'actifs', fn ($q) => $q->where('actif', true))
            ->when($this->filterStatut === 'inactifs', fn ($q) => $q->where('actif', false))
            ->when($this->filterStock === 'alerte', fn ($q) => $q->where('est_stockable', true)->havingRaw('COALESCE(stock_total, 0) <= stock_min'))
            ->when($this->filterStock === 'rupture', fn ($q) => $q->where('est_stockable', true)->havingRaw('COALESCE(stock_total, 0) <= 0'))
            ->orderBy('designation')
            ->paginate(10);

        $categories = Category::orderBy('nom')->get();
        $magasins = $this->magasinsAutorises();
        // Multi-magasins ? on affiche le sélecteur (filtre + formulaire) même pour un non-admin.
        $showMagasinSelector = $ids === null || count($ids) > 1;

        $nbProduitsTotal = Produit::when($ids !== null, fn ($q) => $q->whereIn('magasin_id', $ids))->count();
        $valeurStock = (float) DB::table('stocks')
            ->join('produits', 'produits.id', '=', 'stocks.produit_id')
            ->when($ids !== null, fn ($q) => $q->whereIn('produits.magasin_id', $ids))
            ->selectRaw('COALESCE(SUM(stocks.quantite * produits.prix_achat), 0) as total')
            ->value('total');

        $subtitle = "{$nbProduitsTotal} produit(s)";
        if ($ids !== null && ! $showMagasinSelector && auth()->user()->magasin) {
            $subtitle .= " <span class='text-slate-400'>(" . auth()->user()->magasin->nom . ' uniquement)</span>';
        }
        $subtitle .= " - <span class='text-emerald-600 font-semibold'>" . number_format($valeurStock, 0, ',', ' ') . ' F CFA</span> en stock';

        return view('livewire.produits.index', compact(
            'produits', 'categories', 'magasins', 'nbProduitsTotal', 'valeurStock', 'subtitle', 'showMagasinSelector'
        ))->layout('layouts.app', ['title' => 'Produits']);
    }
}
