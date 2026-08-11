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

    public function magasin()
    {
        return $this->belongsTo(Magasin::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    /**
     * true si l'utilisateur a l'un des rôles donnés (super_admin passe toujours).
     */
    public function hasRole(string ...$roles): bool
    {
        return $this->role === 'super_admin' || in_array($this->role, $roles, true);
    }

    /**
     * true si l'utilisateur voit les données de toute la boutique (ventes, achats,
     * mouvements de stock de tous les employés). false = ne voit que les siennes.
     * Seuls admin et super_admin ont une vision globale.
     */
    public function hasFullAccess(): bool
    {
        return $this->isAdmin();
    }
}
