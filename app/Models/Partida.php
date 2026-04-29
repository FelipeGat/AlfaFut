<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partida extends Model
{
    use HasFactory;

    protected $fillable = [
        'patota_id',
        'local_id',
        'organizador_id',
        'titulo',
        'descricao',
        'data_hora',
        'duracao_minutos',
        'vagas_total',
        'valor_individual',
        'status',
        'confirmacao_ate',
        'lista_espera_habilitada',
    ];

    protected $casts = [
        'data_hora' => 'datetime',
        'confirmacao_ate' => 'datetime',
        'lista_espera_habilitada' => 'boolean',
        'valor_individual' => 'decimal:2',
    ];

    public function patota(): BelongsTo
    {
        return $this->belongsTo(Patota::class);
    }

    public function local(): BelongsTo
    {
        return $this->belongsTo(Local::class);
    }

    public function organizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizador_id');
    }

    public function confirmacoes(): HasMany
    {
        return $this->hasMany(PartidaConfirmacao::class);
    }

    public function confirmados(): HasMany
    {
        return $this->confirmacoes()
            ->where('status', 'confirmado')
            ->where('em_lista_espera', false);
    }

    public function listaEspera(): HasMany
    {
        return $this->confirmacoes()
            ->where('em_lista_espera', true)
            ->orderBy('posicao_lista_espera');
    }

    public function times(): HasMany
    {
        return $this->hasMany(Time::class);
    }

    public function despesas(): HasMany
    {
        return $this->hasMany(Despesa::class);
    }

    public function vagasDisponiveis(): int
    {
        return max(0, $this->vagas_total - $this->confirmados()->count());
    }

    public function isCheia(): bool
    {
        return $this->vagasDisponiveis() === 0;
    }

    public function scopeProximas(Builder $query): Builder
    {
        return $query->where('data_hora', '>=', now())
            ->whereIn('status', ['agendada', 'confirmada'])
            ->orderBy('data_hora');
    }

    public function scopePassadas(Builder $query): Builder
    {
        return $query->where('data_hora', '<', now())
            ->orderByDesc('data_hora');
    }
}
