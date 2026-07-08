<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone_pro',
        'telephone_mobile',
        'photo_profil',
        'password',
        'role',
        'est_actif',
        'derniere_connexion_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'derniere_connexion_at' => 'datetime',
        'est_actif' => 'boolean',
        'password' => 'hashed',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isChefProjet(): bool
    {
        return $this->role === 'chef_projet';
    }

    public function isMagasinier(): bool
    {
        return $this->role === 'magasinier';
    }

    public function getNomCompletAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    public function isResponsable(): bool
    {
        return $this->role === 'responsable';
    }
    
    public function documents()
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    public function permissions(): array
    {
        $map = config('permissions.roles', []);
        $rolePerms = $map[$this->role] ?? [];

        if ($rolePerms === '*') {
            return config('permissions.all', []);
        }

        return $rolePerms;
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        return in_array($permission, $this->permissions(), true);
    }

    public function updateLastLogin(): void
    {
        $this->derniere_connexion_at = now();
        $this->save();
    }
}