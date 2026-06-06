<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'apelido',
        'email',
        'telefone',
        'avatar_url',
        'data_nascimento',
        'posicao_preferida',
        'nivel_habilidade',
        'password',
        'role',
        'tipo_usuario',
        'alto_contraste',
        'tamanho_fonte',
        'reduzir_movimento',
        'leitor_tela_otimizado',
        'necessidades_acessibilidade',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'data_nascimento' => 'date',
            'password' => 'hashed',
            'alto_contraste' => 'boolean',
            'reduzir_movimento' => 'boolean',
            'leitor_tela_otimizado' => 'boolean',
            'necessidades_acessibilidade' => 'array',
        ];
    }

    public function patotasCriadas(): HasMany
    {
        return $this->hasMany(Patota::class, 'criador_id');
    }

    public function patotas(): BelongsToMany
    {
        return $this->belongsToMany(Patota::class, 'patota_membros')
            ->withPivot(['papel', 'status', 'entrou_em', 'saiu_em'])
            ->withTimestamps();
    }

    public function partidasOrganizadas(): HasMany
    {
        return $this->hasMany(Partida::class, 'organizador_id');
    }

    public function confirmacoes(): HasMany
    {
        return $this->hasMany(PartidaConfirmacao::class);
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class);
    }

    public function nomeExibicao(): string
    {
        return $this->apelido ?: $this->name;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
