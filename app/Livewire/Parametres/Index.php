<?php

namespace App\Livewire\Parametres;

use App\Models\PaymentMethod;
use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    // Informations boutique
    public $logo = null; // fichier temporaire uploadé
    public string $nom = '';
    public string $telephone = '';
    public string $email = '';
    public string $adresse = '';
    public string $ville = '';
    public string $pays = '';
    public string $ninea = '';
    public string $rccm = '';

    // Paramètres facture
    public ?float $tva_defaut = 18;
    public string $format_num_facture = '';
    public string $mentions_legales = '';

    // Notifications
    public bool $alerte_stock_bas = true;
    public bool $creances_echeance = true;
    public bool $rapports_quotidiens = false;
    public bool $activer_multi_magasin = false;

    // Modes de paiement
    public bool $showPaymentModal = false;
    public ?int $editingPaymentId = null;
    public string $pm_nom = '';
    public string $pm_code = '';
    public ?int $confirmingDeleteId = null;
    public string $confirmMessage = 'Ce mode de paiement sera définitivement supprimé.';

    public function mount()
    {
        $tenant = auth()->user()->tenant;
        $this->nom = $tenant->nom;
        $this->telephone = (string) $tenant->telephone;
        $this->email = (string) $tenant->email;
        $this->adresse = (string) $tenant->adresse;
        $this->ville = (string) $tenant->ville;
        $this->pays = (string) ($tenant->pays ?? 'Sénégal');
        $this->ninea = (string) $tenant->ninea;
        $this->rccm = (string) $tenant->rccm;
        $this->tva_defaut = $tenant->tva_defaut;
        $this->format_num_facture = (string) ($tenant->format_num_facture ?? 'FAC-{YYYY}{MM}{DD}-{NNN}');
        $this->mentions_legales = (string) $tenant->mentions_legales;
        $this->alerte_stock_bas = (bool) $tenant->alerte_stock_bas;
        $this->creances_echeance = (bool) $tenant->creances_echeance;
        $this->rapports_quotidiens = (bool) $tenant->rapports_quotidiens;
        $this->activer_multi_magasin = (bool) $tenant->activer_multi_magasin;
    }

    public function save()
    {
        $data = $this->validate([
            'nom' => 'required|string|max:255',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:255',
            'pays' => 'nullable|string|max:255',
            'ninea' => 'nullable|string|max:100',
            'rccm' => 'nullable|string|max:100',
            'tva_defaut' => 'nullable|numeric|min:0|max:100',
            'format_num_facture' => 'nullable|string|max:100',
            'mentions_legales' => 'nullable|string|max:1000',
            'alerte_stock_bas' => 'boolean',
            'creances_echeance' => 'boolean',
            'rapports_quotidiens' => 'boolean',
            'activer_multi_magasin' => 'boolean',
        ]);

        $tenant = auth()->user()->tenant;

        if ($this->logo) {
            $this->validate(['logo' => 'image|max:1024']);
            $path = $this->logo->store('logos', 'public');
            $data['logo_url'] = Storage::url($path);
        }

        $tenant->update($data);
        $this->logo = null;

        session()->flash('success', 'Paramètres mis à jour.');
    }

    // --- Modes de paiement ---

    public function openPaymentModal(?int $id = null)
    {
        if ($id) {
            $pm = PaymentMethod::findOrFail($id);
            $this->editingPaymentId = $pm->id;
            $this->pm_nom = $pm->nom;
            $this->pm_code = $pm->code;
        } else {
            $this->editingPaymentId = null;
            $this->pm_nom = '';
            $this->pm_code = '';
        }
        $this->showPaymentModal = true;
    }

    public function savePayment()
    {
        $data = $this->validate([
            'pm_nom' => 'required|string|max:255',
            'pm_code' => 'required|string|max:50',
        ], [], ['pm_nom' => 'nom', 'pm_code' => 'code']);

        if ($this->editingPaymentId) {
            PaymentMethod::findOrFail($this->editingPaymentId)->update([
                'nom' => $data['pm_nom'],
                'code' => $data['pm_code'],
            ]);
        } else {
            PaymentMethod::create([
                'nom' => $data['pm_nom'],
                'code' => $data['pm_code'],
                'actif' => true,
            ]);
        }

        $this->showPaymentModal = false;
        session()->flash('success', 'Mode de paiement enregistré.');
    }

    public function togglePaymentActif(int $id)
    {
        $pm = PaymentMethod::findOrFail($id);
        $pm->update(['actif' => ! $pm->actif]);
    }

    public function confirmDelete(int $id)
    {
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
        PaymentMethod::findOrFail($this->confirmingDeleteId)->delete();
        $this->confirmingDeleteId = null;
        session()->flash('success', 'Mode de paiement supprimé.');
    }

    public function render()
    {
        $paymentMethods = PaymentMethod::orderBy('nom')->get();
        $tenant = auth()->user()->tenant;

        return view('livewire.parametres.index', compact('paymentMethods', 'tenant'))
            ->layout('layouts.app', ['title' => 'Paramètres']);
    }
}
