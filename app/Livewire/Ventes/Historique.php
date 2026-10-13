<?php

namespace App\Livewire\Ventes;

use App\Exceptions\InsufficientStockException;
use App\Models\Client;
use App\Models\Creance;
use App\Models\Facture;
use App\Models\FactureItem;
use App\Models\Produit;
use App\Services\StockService;
use App\Support\DocNumber;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Historique extends Component
{
    use WithPagination;

    #[Url(as: 'search')]
    public string $search = '';
    #[Url(as: 'statut')]
    public string $filterStatut = '';
    #[Url(as: 'periode')]
    public string $filterPeriode = '';
    #[Url(as: 'type')]
    public string $filterType = ''; // '' = tout, 'facture', 'proforma'

    // --- Conversion proforma -> facture ---
    public ?int $convertFactureId = null;
    public string $convertPaiement = 'paye'; // 'paye' ou 'credit'

    // --- Modale de modification ---
    public bool $showEditModal = false;
    public ?int $editFactureId = null;
    public ?int $editClientId = null;
    public string $editClientSearch = '';
    public bool $editClientDropdownOpen = false;
    public string $editDate = '';
    public string $editNotes = '';
    public bool $editInclureTva = false;
    public float $editMontantPaye = 0;
    public bool $editEstProforma = false;
    public array $editLignes = []; // [['product_id','designation','qte','prix_unitaire','total_ht']]

    // Recherche produit pour ajouter une ligne dans la modale
    public string $editProduitSearch = '';
    public bool $editProduitDropdownOpen = false;
    public ?int $editLigneProduitId = null;
    public float $editLigneQte = 1;
    public float $editLignePrix = 0;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterStatut() { $this->resetPage(); }
    public function updatingFilterPeriode() { $this->resetPage(); }
    public function updatingFilterType() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'filterStatut', 'filterPeriode', 'filterType']);
        $this->resetPage();
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

    protected function peutModifier(Facture $facture): bool
    {
        if ($facture->statut === 'annulee') {
            return false;
        }

        return auth()->user()->hasFullAccess() || $facture->utilisateur_id === auth()->id();
    }

    public function openEdit(int $factureId)
    {
        $facture = Facture::with('items')->findOrFail($factureId);

        if (! $this->peutModifier($facture)) {
            session()->flash('error', "Vous n'avez pas le droit de modifier cette facture.");
            return;
        }

        $this->editFactureId = $facture->id;
        $this->editClientId = $facture->client_id;
        $this->editClientSearch = $facture->client ? trim($facture->client->nom . ' ' . $facture->client->prenom) : '';
        $this->editDate = $facture->date_facture->toDateString();
        $this->editNotes = (string) $facture->notes;
        $this->editInclureTva = (float) $facture->taux_tva > 0;
        $this->editMontantPaye = (float) $facture->montant_paye;
        $this->editEstProforma = $facture->type_doc === 'devis';

        $this->editLignes = $facture->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'designation' => $item->designation,
            'qte' => (float) $item->qte,
            'prix_unitaire' => (float) $item->prix_unitaire,
            'total_ht' => (float) $item->total_ht,
        ])->all();

        $this->reset(['editProduitSearch', 'editLigneProduitId', 'editLignePrix']);
        $this->editLigneQte = 1;
        $this->showEditModal = true;
    }

    public function selectClientEdit(?int $id)
    {
        $this->editClientId = $id;
        if ($id) {
            $client = Client::find($id);
            $this->editClientSearch = $client ? trim($client->nom . ' ' . $client->prenom) : '';
        } else {
            $this->editClientSearch = '';
        }
        $this->editClientDropdownOpen = false;
    }

    public function selectProduitEdit(int $id)
    {
        $produit = Produit::find($id);
        if (! $produit) {
            return;
        }
        $this->editLigneProduitId = $produit->id;
        $this->editProduitSearch = $produit->designation;
        $this->editLignePrix = (float) $produit->prix_vente;
        $this->editProduitDropdownOpen = false;
    }

    public function ajouterLigneEdit()
    {
        $this->validate([
            'editLigneProduitId' => 'required|exists:produits,id',
            'editLigneQte' => 'required|numeric|gt:0',
            'editLignePrix' => 'required|numeric|min:0',
        ], [], ['editLigneProduitId' => 'produit']);

        $produit = Produit::findOrFail($this->editLigneProduitId);

        $this->editLignes[] = [
            'product_id' => $produit->id,
            'designation' => $produit->designation,
            'qte' => $this->editLigneQte,
            'prix_unitaire' => $this->editLignePrix,
            'total_ht' => $this->editLigneQte * $this->editLignePrix,
        ];

        $this->reset(['editLigneProduitId', 'editLignePrix', 'editProduitSearch']);
        $this->editLigneQte = 1;
    }

    public function retirerLigneEdit(int $index)
    {
        unset($this->editLignes[$index]);
        $this->editLignes = array_values($this->editLignes);
    }

    public function getEditSousTotalProperty(): float
    {
        return collect($this->editLignes)->sum('total_ht');
    }

    public function getEditTvaMontantProperty(): float
    {
        if (! $this->editInclureTva) {
            return 0;
        }
        $taux = (float) (auth()->user()->tenant->tva_defaut ?? 18);

        return $this->editSousTotal * ($taux / 100);
    }

    public function getEditTotalProperty(): float
    {
        return $this->editSousTotal + $this->editTvaMontant;
    }

    public function saveEdit(StockService $stockService)
    {
        $facture = Facture::with('items')->find($this->editFactureId);

        if (! $facture || ! $this->peutModifier($facture)) {
            session()->flash('error', 'Action non autorisée.');
            $this->showEditModal = false;
            return;
        }

        $estProforma = $facture->type_doc === 'devis';

        $this->validate([
            'editDate' => 'required|date',
            'editMontantPaye' => $estProforma ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
        ]);

        if (empty($this->editLignes)) {
            session()->flash('error', "Une facture doit contenir au moins un produit.");
            return;
        }

        $sousTotal = $this->editSousTotal;
        $tva = $this->editTvaMontant;
        $total = $this->editTotal;
        $tauxTva = $this->editInclureTva ? (float) (auth()->user()->tenant->tva_defaut ?? 18) : 0;
        $montantPaye = $estProforma ? 0.0 : min($this->editMontantPaye, $total);

        try {
            DB::transaction(function () use ($facture, $stockService, $sousTotal, $tva, $tauxTva, $total, $montantPaye, $estProforma) {
                // Capturé AVANT toute modification : nécessaire pour recalculer correctement
                // le delta de créance plus bas (après update(), l'original serait déjà écrasé).
                $ancienMontantTtc = (float) $facture->montant_ttc;
                $ancienMontantPaye = (float) $facture->montant_paye;

                // 1) Réajuste le stock : compare les anciennes quantités par produit
                //    aux nouvelles, et ne mouvemente que la différence.
                $ancien = $facture->items->groupBy('product_id')
                    ->map(fn ($items) => (float) $items->sum('qte'));

                $nouveau = collect($this->editLignes)->groupBy('product_id')
                    ->map(fn ($lignes) => (float) collect($lignes)->sum('qte'));

                $produitIds = $ancien->keys()->merge($nouveau->keys())->unique()->filter();

                foreach ($produitIds as $produitId) {
                    $produit = Produit::find($produitId);
                    // Une proforma n'a jamais touché au stock : rien à réajuster.
                    if ($estProforma || ! $produit || ! $produit->est_stockable) {
                        continue;
                    }

                    $qteAvant = (float) ($ancien[$produitId] ?? 0);
                    $qteApres = (float) ($nouveau[$produitId] ?? 0);
                    $delta = $qteApres - $qteAvant;

                    if ($delta > 0) {
                        // Vendu en plus qu'avant : sortie de stock supplémentaire.
                        $stockService->sortie($produit, $facture->magasin_id, $delta, 'Correction facture ' . $facture->num_facture, $facture->num_facture);
                    } elseif ($delta < 0) {
                        // Vendu en moins qu'avant (ou ligne supprimée) : le stock revient.
                        $stockService->entree($produit, $facture->magasin_id, abs($delta), 'Correction facture ' . $facture->num_facture, $facture->num_facture);
                    }
                }

                // 2) Remplace les lignes de la facture.
                $facture->items()->delete();
                foreach ($this->editLignes as $ligne) {
                    FactureItem::create([
                        'facture_id' => $facture->id,
                        'product_id' => $ligne['product_id'],
                        'designation' => $ligne['designation'],
                        'qte' => $ligne['qte'],
                        'prix_unitaire' => $ligne['prix_unitaire'],
                        'total_ht' => $ligne['total_ht'],
                    ]);
                }

                // 3) Recalcule le statut et met à jour la facture.
                $statut = $estProforma ? 'brouillon' : ($montantPaye >= $total ? 'payee' : ($montantPaye > 0 ? 'partiel' : 'brouillon'));

                $facture->update([
                    'client_id' => $this->editClientId,
                    'date_facture' => $this->editDate,
                    'notes' => $this->editNotes ?: null,
                    'montant_ht' => $sousTotal,
                    'taux_tva' => $tauxTva,
                    'tva' => $tva,
                    'montant_ttc' => $total,
                    'montant_paye' => $montantPaye,
                    'statut' => $statut,
                ]);

                if ($estProforma) {
                    return; // pas de créance ni de solde client pour une proforma
                }

                // 4) Réajuste la créance client et son solde en fonction du nouveau "reste à payer".
                $ancienReste = max(0, $ancienMontantTtc - $ancienMontantPaye);
                $nouveauReste = max(0, $total - $montantPaye);
                $deltaReste = $nouveauReste - $ancienReste;

                if ($this->editClientId && abs($deltaReste) > 0.01) {
                    Client::where('id', $this->editClientId)->increment('solde_creance', $deltaReste);
                }

                $creance = Creance::where('facture_id', $facture->id)->first();

                if ($nouveauReste <= 0.01) {
                    // Facture soldée : la créance associée n'a plus lieu d'être.
                    $creance?->delete();
                } elseif ($creance) {
                    $creance->update([
                        'montant_initial' => $total,
                        'montant_restant' => $nouveauReste,
                        'montant_acompte' => $montantPaye,
                        'statut' => $montantPaye > 0 ? 'partiel' : 'en_cours',
                    ]);
                } elseif ($this->editClientId) {
                    Creance::create([
                        'client_id' => $this->editClientId,
                        'facture_id' => $facture->id,
                        'montant_initial' => $total,
                        'montant_restant' => $nouveauReste,
                        'montant_acompte' => $montantPaye,
                        'statut' => $montantPaye > 0 ? 'partiel' : 'en_cours',
                    ]);
                }
            });
        } catch (InsufficientStockException $e) {
            session()->flash('error', $e->getMessage() . ' Ajuste les quantités et réessaie.');
            return;
        }

        session()->flash('success', $estProforma ? 'Proforma modifiée avec succès.' : 'Facture modifiée avec succès.');
        $this->showEditModal = false;
    }

    // ------------------------------------------------------------------
    //  Proforma -> facture
    // ------------------------------------------------------------------

    public function openConvert(int $factureId)
    {
        $facture = Facture::findOrFail($factureId);

        if ($facture->type_doc !== 'devis' || ! $this->peutModifier($facture)) {
            session()->flash('error', 'Cette proforma ne peut pas être convertie.');
            return;
        }

        $this->convertFactureId = $facture->id;
        $this->convertPaiement = 'paye';
    }

    public function cancelConvert()
    {
        $this->reset(['convertFactureId']);
        $this->convertPaiement = 'paye';
    }

    /**
     * Transforme la proforma en vraie facture : nouveau numéro FA-…, sortie de stock
     * (bloquée si le stock a baissé depuis), et paiement enregistré ou créance ouverte.
     */
    public function convertir(StockService $stockService)
    {
        $facture = Facture::with('items')->find($this->convertFactureId);

        if (! $facture || $facture->type_doc !== 'devis' || ! $this->peutModifier($facture)) {
            session()->flash('error', 'Action non autorisée.');
            $this->cancelConvert();
            return;
        }

        $aCredit = $this->convertPaiement === 'credit' && $facture->client_id;
        $total = (float) $facture->montant_ttc;
        $ancienNum = $facture->num_facture;
        $nouveauNum = '';

        try {
            DB::transaction(function () use ($facture, $stockService, $aCredit, $total, $ancienNum, &$nouveauNum) {
                $nouveauNum = DocNumber::next('FA');

                // Sortie de stock, comme une vente normale.
                foreach ($facture->items as $item) {
                    $produit = $item->product_id ? Produit::find($item->product_id) : null;

                    if ($produit && $produit->est_stockable) {
                        $stockService->sortie($produit, $facture->magasin_id, (float) $item->qte, 'Vente ' . $nouveauNum, $nouveauNum);
                    }
                }

                $facture->update([
                    'type_doc' => 'facture',
                    'num_facture' => $nouveauNum,
                    'date_facture' => today(),
                    'montant_paye' => $aCredit ? 0 : $total,
                    'statut' => $aCredit ? 'brouillon' : 'payee',
                    'notes' => trim(($facture->notes ? $facture->notes . "\n" : '') . 'Issue de la proforma ' . $ancienNum),
                ]);

                if ($aCredit && $total > 0) {
                    Creance::create([
                        'client_id' => $facture->client_id,
                        'facture_id' => $facture->id,
                        'montant_initial' => $total,
                        'montant_restant' => $total,
                        'montant_acompte' => 0,
                        'statut' => 'en_cours',
                    ]);
                    Client::where('id', $facture->client_id)->increment('solde_creance', $total);
                }
            });
        } catch (InsufficientStockException $e) {
            session()->flash('error', $e->getMessage() . ' La proforma reste inchangée : réapprovisionne ou modifie-la, puis réessaie.');
            $this->cancelConvert();
            return;
        }

        $this->cancelConvert();
        session()->flash('success', "Proforma {$ancienNum} convertie en facture {$nouveauNum}. Le stock a été mis à jour.");
    }

    public function render()
    {
        $debut = $this->debutPeriode();

        $query = Facture::with(['client', 'utilisateur'])
            ->when(! auth()->user()->hasFullAccess(), fn ($q) => $q->where('utilisateur_id', auth()->id()))
            ->when($this->search, fn ($q) => $q->where(function ($q2) {
                $q2->where('num_facture', 'ilike', "%{$this->search}%")
                   ->orWhereHas('client', fn ($q3) => $q3->where('nom', 'ilike', "%{$this->search}%"));
            }))
            ->when($this->filterType === 'proforma', fn ($q) => $q->where('type_doc', 'devis'))
            ->when($this->filterType === 'facture', fn ($q) => $q->where('type_doc', '!=', 'devis'))
            ->when($this->filterStatut, fn ($q) => $q->where('statut', $this->filterStatut))
            ->when($debut, fn ($q) => $q->whereDate('date_facture', '>=', $debut));

        // Le chiffre d'affaires ne compte jamais les proformas (ce ne sont pas des ventes).
        $totalPeriode = (clone $query)->where('type_doc', '!=', 'devis')->sum('montant_ttc');

        $factures = $query->latest('date_facture')->latest('id')->paginate(10);

        $clientsFiltres = Client::when($this->editClientSearch, fn ($q) => $q->where(function ($q2) {
                $q2->where('nom', 'ilike', "%{$this->editClientSearch}%")
                   ->orWhere('prenom', 'ilike', "%{$this->editClientSearch}%");
            }))
            ->orderBy('nom')
            ->limit(8)
            ->get();

        $produitsFiltres = Produit::when($this->editProduitSearch, fn ($q) => $q->where('designation', 'ilike', "%{$this->editProduitSearch}%"))
            ->orderBy('designation')
            ->limit(8)
            ->get();

        $subtitle = "{$factures->total()} document(s) - <span class='font-semibold text-ink-950'>" . number_format($totalPeriode, 0, ',', ' ') . ' F CFA</span>';
        if (! auth()->user()->hasFullAccess()) {
            $subtitle .= " <span class='text-slate-400'>(mes ventes uniquement)</span>";
        }

        return view('livewire.ventes.historique', compact('factures', 'totalPeriode', 'subtitle', 'clientsFiltres', 'produitsFiltres'))
            ->layout('layouts.app', ['title' => 'Ventes']);
    }
}
