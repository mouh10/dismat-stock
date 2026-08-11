<?php

namespace App\Livewire\Achats;

use App\Models\Achat;
use App\Models\AchatItem;
use App\Models\DetteFournisseur;
use App\Models\Fournisseur;
use App\Models\Magasin;
use App\Models\Produit;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'search')]
    public string $search = '';
    #[Url(as: 'statut')]
    public string $filterStatut = '';
    #[Url(as: 'fournisseur')]
    public string $filterFournisseur = '';
    #[Url(as: 'periode')]
    public string $filterPeriode = '';

    public bool $showModal = false;

    // Formulaire nouvel achat
    public ?int $fournisseur_id = null;
    public string $reference = '';
    public string $date_achat;
    public string $mode_paiement = 'especes';
    public array $lignes = []; // [['product_id'=>, 'designation'=>, 'qte'=>, 'prix_unitaire'=>]]
    public float $montant_paye = 0;
    public string $notes = '';
    public bool $inclureTva = false;

    // Recherche produit (ajout de ligne)
    public string $produitSearch = '';
    public bool $produitDropdownOpen = false;
    public ?int $ligne_produit_id = null;
    public float $ligne_qte = 1;
    public float $ligne_prix = 0;

    public function mount()
    {
        $this->date_achat = today()->toDateString();
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterStatut() { $this->resetPage(); }
    public function updatingFilterFournisseur() { $this->resetPage(); }
    public function updatingFilterPeriode() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'filterStatut', 'filterFournisseur', 'filterPeriode']);
        $this->resetPage();
    }

    public function openModal()
    {
        $this->reset([
            'fournisseur_id', 'reference', 'lignes', 'montant_paye', 'notes', 'inclureTva',
            'produitSearch', 'ligne_produit_id', 'ligne_prix',
        ]);
        $this->date_achat = today()->toDateString();
        $this->mode_paiement = 'especes';
        $this->ligne_qte = 1;
        $this->showModal = true;
    }

    public function selectProduit(int $id)
    {
        $produit = Produit::find($id);
        if (! $produit) {
            return;
        }
        $this->ligne_produit_id = $produit->id;
        $this->produitSearch = $produit->designation;
        $this->ligne_prix = (float) $produit->prix_achat;
        $this->produitDropdownOpen = false;
    }

    public function ajouterLigne()
    {
        $this->validate([
            'ligne_produit_id' => 'required|exists:produits,id',
            'ligne_qte' => 'required|numeric|gt:0',
            'ligne_prix' => 'required|numeric|min:0',
        ], [], ['ligne_produit_id' => 'produit']);

        $produit = Produit::findOrFail($this->ligne_produit_id);

        $this->lignes[] = [
            'product_id' => $produit->id,
            'designation' => $produit->designation,
            'qte' => $this->ligne_qte,
            'prix_unitaire' => $this->ligne_prix,
            'total_ht' => $this->ligne_qte * $this->ligne_prix,
        ];

        $this->reset(['ligne_produit_id', 'ligne_prix', 'produitSearch']);
        $this->ligne_qte = 1;
    }

    public function retirerLigne(int $index)
    {
        unset($this->lignes[$index]);
        $this->lignes = array_values($this->lignes);
    }

    public function getSousTotalProperty(): float
    {
        return collect($this->lignes)->sum('total_ht');
    }

    public function getTvaMontantProperty(): float
    {
        if (! $this->inclureTva) {
            return 0;
        }
        $taux = (float) (auth()->user()->tenant->tva_defaut ?? 18);

        return $this->sousTotal * ($taux / 100);
    }

    public function getTotalAchatProperty(): float
    {
        return $this->sousTotal + $this->tvaMontant;
    }

    public function enregistrer(StockService $stockService)
    {
        $this->validate([
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'date_achat' => 'required|date',
        ]);

        if (empty($this->lignes)) {
            session()->flash('error', "Ajoutez au moins un produit à l'achat.");
            return;
        }

        $sousTotal = $this->sousTotal;
        $tva = $this->tvaMontant;
        $total = $this->totalAchat;

        if ($this->montant_paye < 0) {
            $this->addError('montant_paye', 'Le montant payé ne peut pas être négatif.');
            return;
        }

        if ($this->montant_paye > $total) {
            $this->addError('montant_paye', 'Le montant payé ne peut pas dépasser le total (' . number_format($total, 0, ',', ' ') . ' F).');
            return;
        }

        $montantPaye = $this->montant_paye;
        $tenant = auth()->user()->tenant;
        $tauxTva = $this->inclureTva ? (float) ($tenant->tva_defaut ?? 18) : 0;

        DB::transaction(function () use ($sousTotal, $tva, $tauxTva, $total, $montantPaye, $stockService) {
            $numAchat = 'ACH-' . now()->format('Y') . '-' . str_pad((string) (Achat::whereYear('created_at', now()->year)->count() + 1), 3, '0', STR_PAD_LEFT);

            $achat = Achat::create([
                'fournisseur_id' => $this->fournisseur_id,
                'utilisateur_id' => auth()->id(),
                'num_achat' => $numAchat,
                'reference' => $this->reference ?: null,
                'montant_ht' => $sousTotal,
                'taux_tva' => $tauxTva,
                'inclure_tva' => $this->inclureTva,
                'tva' => $tva,
                'montant_ttc' => $total,
                'montant_paye' => $montantPaye,
                'mode_paiement' => $this->mode_paiement,
                'statut_paiement' => $montantPaye <= 0 ? 'non_regle' : ($montantPaye < $total ? 'partiel' : 'regle'),
                'date_achat' => $this->date_achat,
                'notes' => $this->notes ?: null,
            ]);

            $magasin = Magasin::where('est_principal', true)->first() ?? Magasin::first();

            foreach ($this->lignes as $ligne) {
                AchatItem::create([
                    'achat_id' => $achat->id,
                    'product_id' => $ligne['product_id'],
                    'designation' => $ligne['designation'],
                    'qte' => $ligne['qte'],
                    'prix_unitaire' => $ligne['prix_unitaire'],
                    'total_ht' => $ligne['total_ht'],
                ]);

                if ($magasin) {
                    $produit = Produit::find($ligne['product_id']);
                    if ($produit && $produit->est_stockable) {
                        $stockService->entree($produit, $magasin->id, $ligne['qte'], 'Achat ' . $achat->num_achat, $achat->num_achat);
                    }
                }
            }

            if ($montantPaye < $total) {
                DetteFournisseur::create([
                    'fournisseur_id' => $this->fournisseur_id,
                    'achat_id' => $achat->id,
                    'montant_initial' => $total,
                    'montant_restant' => $total - $montantPaye,
                    'statut' => $montantPaye > 0 ? 'partiel' : 'en_cours',
                ]);
            }
        });

        session()->flash('success', 'Achat enregistré avec succès.');
        $this->showModal = false;
    }

    protected function debutPeriode(): ?\Illuminate\Support\Carbon
    {
        return match ($this->filterPeriode) {
            'jour' => today(),
            'semaine' => now()->startOfWeek(),
            'mois' => now()->startOfMonth(),
            'annee' => now()->startOfYear(),
            default => null,
        };
    }

    public function render()
    {
        $debut = $this->debutPeriode();

        $achats = Achat::with('fournisseur')
            ->when(! auth()->user()->hasFullAccess(), fn ($q) => $q->where('utilisateur_id', auth()->id()))
            ->when($this->search, fn ($q) => $q->where(function ($q2) {
                $q2->where('num_achat', 'ilike', "%{$this->search}%")
                   ->orWhere('reference', 'ilike', "%{$this->search}%")
                   ->orWhereHas('fournisseur', fn ($q3) => $q3->where('nom', 'ilike', "%{$this->search}%"));
            }))
            ->when($this->filterStatut, fn ($q) => $q->where('statut_paiement', $this->filterStatut))
            ->when($this->filterFournisseur, fn ($q) => $q->where('fournisseur_id', $this->filterFournisseur))
            ->when($debut, fn ($q) => $q->whereDate('date_achat', '>=', $debut))
            ->latest('date_achat')
            ->latest('id')
            ->paginate(10);

        $fournisseurs = Fournisseur::orderBy('nom')->get();

        $produitsFiltres = Produit::when($this->produitSearch, fn ($q) => $q->where('designation', 'ilike', "%{$this->produitSearch}%"))
            ->orderBy('designation')
            ->limit(8)
            ->get();

        $achatsBase = Achat::when(! auth()->user()->hasFullAccess(), fn ($q) => $q->where('utilisateur_id', auth()->id()));
        $nbTotal = (clone $achatsBase)->count();
        $totalAchats = (float) (clone $achatsBase)->sum('montant_ttc');
        $nonRegle = (float) (clone $achatsBase)->whereIn('statut_paiement', ['non_regle', 'partiel'])
            ->get()
            ->sum(fn ($a) => (float) $a->montant_ttc - (float) $a->montant_paye);

        return view('livewire.achats.index', compact(
            'achats', 'fournisseurs', 'produitsFiltres', 'nbTotal', 'totalAchats', 'nonRegle'
        ))->layout('layouts.app', ['title' => 'Achats']);
    }
}
