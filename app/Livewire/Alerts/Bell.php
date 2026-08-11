<?php

namespace App\Livewire\Alerts;

use App\Models\Creance;
use App\Models\Produit;
use Livewire\Attributes\On;
use Livewire\Component;

class Bell extends Component
{
    public bool $open = false;

    public function toggle()
    {
        $this->open = ! $this->open;
    }

    #[On('stock-changed')]
    public function refresh()
    {
        // no-op : force juste un nouveau render à chaque appel de l'event
    }

    public function render()
    {
        $user = auth()->user();
        $peutVoirStock = $user->hasRole('admin', 'gestionnaire');
        $peutVoirCreances = $user->hasRole('admin', 'gestionnaire');

        $produitsStockBas = collect();
        if ($peutVoirStock) {
            $produitsStockBas = Produit::where('est_stockable', true)
                ->where('actif', true)
                ->withSum('stocks as stock_total', 'quantite')
                ->get()
                ->filter(fn ($p) => (float) ($p->stock_total ?? 0) <= $p->stock_min)
                ->take(5);
        }

        $creancesEnRetard = collect();
        if ($peutVoirCreances) {
            $creancesEnRetard = Creance::with('client')
                ->whereIn('statut', ['en_cours', 'partiel'])
                ->whereNotNull('date_echeance')
                ->where('date_echeance', '<', today())
                ->latest('date_echeance')
                ->take(5)
                ->get();
        }

        $total = $produitsStockBas->count() + $creancesEnRetard->count();

        return view('livewire.alerts.bell', compact('produitsStockBas', 'creancesEnRetard', 'total'));
    }
}
