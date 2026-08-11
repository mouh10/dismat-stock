<?php

namespace App\Livewire\Stocks;

use App\Exceptions\InsufficientStockException;
use App\Models\Magasin;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'onglet')]
    public string $activeTab = 'etat'; // etat | mouvements

    public string $search = '';
    public ?int $magasin_id = null;
    public bool $filterStockBas = false;

    public bool $showAdjustModal = false;
    public ?int $adjustProduitId = null;
    public string $adjustType = 'entree'; // entree | sortie
    public float $adjustQte = 0;
    public string $adjustMotif = '';

    public function mount()
    {
        $this->magasin_id = Magasin::where('est_principal', true)->value('id') ?? Magasin::value('id');
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterStockBas() { $this->resetPage(); }
    public function updatedMagasinId() { $this->resetPage(); }

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterStockBas']);
        $this->resetPage();
    }

    public function openAdjust(int $produitId, string $type)
    {
        $this->adjustProduitId = $produitId;
        $this->adjustType = $type;
        $this->adjustQte = 0;
        $this->adjustMotif = '';
        $this->showAdjustModal = true;
    }

    public function saveAdjust(StockService $stockService)
    {
        $this->validate([
            'adjustQte' => 'required|numeric|gt:0',
            'adjustMotif' => 'nullable|string|max:255',
        ]);

        $produit = Produit::findOrFail($this->adjustProduitId);

        try {
            $stockService->mouvement(
                $produit,
                $this->magasin_id,
                $this->adjustType,
                $this->adjustQte,
                $this->adjustMotif ?: ($this->adjustType === 'entree' ? 'Entrée manuelle' : 'Sortie manuelle')
            );
        } catch (InsufficientStockException $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        session()->flash('success', 'Mouvement de stock enregistré.');
        $this->showAdjustModal = false;
    }

    public function render()
    {
        $magasins = Magasin::orderBy('nom')->get();

        $produits = Produit::where('est_stockable', true)
            ->with(['stocks' => fn ($q) => $q->where('magasin_id', $this->magasin_id), 'category'])
            ->when($this->search, fn ($q) => $q->where('designation', 'ilike', "%{$this->search}%"))
            ->when($this->filterStockBas, function ($q) {
                $q->whereRaw(
                    '(SELECT COALESCE(SUM(quantite), 0) FROM stocks WHERE stocks.produit_id = produits.id AND stocks.magasin_id = ?) <= produits.stock_min',
                    [$this->magasin_id]
                );
            })
            ->orderBy('designation')
            ->paginate(10);

        $mouvements = null;
        if ($this->activeTab === 'mouvements') {
            $mouvements = MouvementStock::with(['produit', 'utilisateur'])
                ->where('magasin_id', $this->magasin_id)
                ->when(! auth()->user()->hasFullAccess(), fn ($q) => $q->where('utilisateur_id', auth()->id()))
                ->latest()
                ->paginate(15);
        }

        // KPI globaux pour le magasin sélectionné
        $statsBase = Produit::where('est_stockable', true)
            ->withSum(['stocks as stock_qte' => fn ($q) => $q->where('magasin_id', $this->magasin_id)], 'quantite')
            ->get();

        $nbArticles = $statsBase->count();
        $valeurTotale = $statsBase->sum(fn ($p) => (float) ($p->stock_qte ?? 0) * (float) $p->prix_achat);
        $nbStockBas = $statsBase->filter(fn ($p) => (float) ($p->stock_qte ?? 0) > 0 && (float) ($p->stock_qte ?? 0) <= $p->stock_min)->count();
        $nbRupture = $statsBase->filter(fn ($p) => (float) ($p->stock_qte ?? 0) <= 0)->count();

        return view('livewire.stocks.index', compact(
            'produits', 'magasins', 'mouvements',
            'nbArticles', 'valeurTotale', 'nbStockBas', 'nbRupture'
        ))->layout('layouts.app', ['title' => 'Stocks']);
    }
}
