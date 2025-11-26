<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'rol_id',
        'name',
        'email',
        'password',
        'telefono',
        'ci_nit',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ===== RELACIONES =====

    /**
     * Relación: Un usuario pertenece a un rol
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class);
    }

    /**
     * Relación: Ventas realizadas como cliente
     */
    public function ventasComoCliente()
    {
        return $this->hasMany(Venta::class, 'cliente_id');
    }

    /**
     * Relación: Ventas realizadas como vendedor
     */
    public function ventasComoVendedor()
    {
        return $this->hasMany(Venta::class, 'vendedor_id');
    }

    // ===== SCOPES =====

    /**
     * Scope: Filtrar por rol
     */
    public function scopePorRol($query, $rolNombre)
    {
        return $query->whereHas('rol', function ($q) use ($rolNombre) {
            $q->where('nombre', $rolNombre);
        });
    }

    /**
     * Scope: Solo clientes
     */
    public function scopeClientes($query)
    {
        return $query->porRol('Cliente');
    }

    /**
     * Scope: Solo vendedores
     */
    public function scopeVendedores($query)
    {
        return $query->porRol('Vendedor');
    }

    /**
     * Scope: Solo administradores
     */
    public function scopeAdministradores($query)
    {
        return $query->porRol('Administrador');
    }

    // ===== MÉTODOS HELPER =====

    /**
     * Verificar si el usuario es administrador
     */
    public function esAdministrador(): bool
    {
        return $this->rol?->nombre === 'Administrador';
    }

    /**
     * Verificar si el usuario es vendedor
     */
    public function esVendedor(): bool
    {
        return $this->rol?->nombre === 'Vendedor';
    }

    /**
     * Verificar si el usuario es cliente
     */
    public function esCliente(): bool
    {
        return $this->rol?->nombre === 'Cliente';
    }
}

