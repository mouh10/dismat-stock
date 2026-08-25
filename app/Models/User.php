<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'tenant_id', 'magasin_id', 'email', 'password', 'nom', 'prenom',
        'telephone', 'role', 'actif', 'derniere_connexion',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'actif' => 'boolean',
            'derniere_connexion' => 'datetime',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Magasin "principal" de l'utilisateur (utilisé par les caissiers pour la Caisse).
     */
    public function magasin()
    {
        return $this->belongsTo(Magasin::class);
    }

    /**
     * Tous les magasins auxquels un gestionnaire a explicitement été rattaché.
     * Table pivot magasin_user (many-to-many).
     */
    public function magasins()
    {
        return $this->belongsToMany(Magasin::class, 'magasin_user')->orderBy('nom');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function hasRole(string ...$roles): bool
    {
        return $this->role === 'super_admin' || in_array($this->role, $roles, true);
    }

    /**
     * true si l'utilisateur voit les données de toute la boutique (tous magasins confondus).
     * Seuls admin et super_admin ont une vision globale.
     */
    public function hasFullAccess(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Liste des identifiants de magasins que cet utilisateur peut voir/gérer.
     * - null   => accès total (admin/super_admin), aucune restriction à appliquer.
     * - array  => uniquement ces magasins (gestionnaire : plusieurs possibles,
     *             caissier : un seul, celui de sa fiche employé).
     * Retourne un tableau vide si aucun magasin n'est assigné (par sécurité,
     * ne montre rien plutôt que tout).
     */
    public function accessibleMagasinIds(): ?array
    {
        if ($this->hasFullAccess()) {
            return null;
        }

        if ($this->role === 'gestionnaire') {
            $ids = $this->magasins()->pluck('magasins.id')->all();

            // Rétro-compatibilité : si aucun magasin n'est encore assigné via la table
            // pivot mais qu'un magasin_id "classique" existe, on l'utilise en secours.
            return count($ids) ? $ids : array_filter([$this->magasin_id]);
        }

        // Caissier : un seul magasin, celui de sa fiche employé.
        return array_filter([$this->magasin_id]);
    }
}
