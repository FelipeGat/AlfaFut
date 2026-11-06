<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements HasLocalePreference, MustVerifyEmail
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
            'excluida_em' => 'datetime',
            'termos_aceitos_em' => 'datetime',
            'data_nascimento' => 'date',
            'password' => 'hashed',
            'alto_contraste' => 'boolean',
            'reduzir_movimento' => 'boolean',
            'leitor_tela_otimizado' => 'boolean',
            'necessidades_acessibilidade' => 'array',
        ];
    }

    /** Em branco vale o padrão da coluna, que não aceita vazio. */
    protected function nivelHabilidade(): Attribute
    {
        return Attribute::make(set: fn ($valor) => $valor ?? 'intermediario');
    }

    /** Só o cadastro define o perfil de uso e o aceite; por isso ficam fora do $fillable. */
    public function concluirCadastro(string $tipoUsuario): void
    {
        $this->forceFill([
            'tipo_usuario' => $tipoUsuario,
            'termos_aceitos_em' => now(),
            'termos_versao' => config('alfafut.versao_termos'),
        ])->save();
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

    public function patotasAtivas(): BelongsToMany
    {
        return $this->patotas()->wherePivot('status', 'ativo');
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

    public function preferredLocale(): string
    {
        return 'pt_BR';
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
