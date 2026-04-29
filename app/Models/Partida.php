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
        'iniciada_em',
        'pausada_em',
        'finalizada_em',
        'tempo_acumulado_segundos',
        'placar_a',
        'placar_b',
    ];

    protected $casts = [
        'data_hora' => 'datetime',
        'confirmacao_ate' => 'datetime',
        'iniciada_em' => 'datetime',
        'pausada_em' => 'datetime',
        'finalizada_em' => 'datetime',
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

    public function eventos(): HasMany
    {
        return $this->hasMany(EventoPartida::class)->orderBy('created_at');
    }

    public function gols(): HasMany
    {
        return $this->eventos()->where('tipo', EventoPartida::TIPO_GOL);
    }

    public function tempoAtualSegundos(): int
    {
        $base = (int) $this->tempo_acumulado_segundos;
        if ($this->iniciada_em && ! $this->pausada_em && ! $this->finalizada_em) {
            $base += (int) abs($this->iniciada_em->diffInSeconds(now()));
        }
        return max(0, $base);
    }

    public function tempoFormatado(): string
    {
        $total = $this->tempoAtualSegundos();
        $min = intdiv($total, 60);
        $seg = $total % 60;
        return sprintf('%02d:%02d', $min, $seg);
    }

    public function emAndamento(): bool
    {
        return $this->iniciada_em !== null
            && $this->pausada_em === null
            && $this->finalizada_em === null;
    }

    public function pausada(): bool
    {
        return $this->pausada_em !== null && $this->finalizada_em === null;
    }

    public function finalizada(): bool
    {
        return $this->finalizada_em !== null;
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
        return $query->where(function ($q) {
            // Agendadas no futuro
            $q->where(function ($w) {
                $w->where('data_hora', '>=', now())
                  ->whereIn('status', ['agendada', 'confirmada']);
            })
            // OU acontecendo agora (em andamento, mesmo se data_hora ja passou)
            ->orWhere('status', 'em_andamento')
            ->orWhereNotNull('iniciada_em')->whereNull('finalizada_em');
        })
        ->orderByDesc('iniciada_em')
        ->orderBy('data_hora');
    }

    public function scopePassadas(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('status', 'finalizada')
              ->orWhereNotNull('finalizada_em')
              ->orWhere(function ($w) {
                  $w->where('data_hora', '<', now())
                    ->whereNotIn('status', ['em_andamento']);
              });
        })
        ->orderByDesc('data_hora');
    }
}
