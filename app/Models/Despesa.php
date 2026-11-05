<?php

namespace App\Models;

use App\Support\Dinheiro;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Despesa extends Model
{
    protected $fillable = [
        'patota_id',
        'partida_id',
        'criada_por_id',
        'descricao',
        'categoria',
        'valor_total',
        'data_despesa',
        'rateada',
        'status',
    ];

    protected $casts = [
        'data_despesa' => 'date',
        'rateada' => 'boolean',
        'valor_total' => 'decimal:2',
    ];

    /** Inclui a turma arquivada: a despesa dela continua aberta para leitura. */
    public function patota(): BelongsTo
    {
        return $this->belongsTo(Patota::class)->withTrashed();
    }

    public function partida(): BelongsTo
    {
        return $this->belongsTo(Partida::class);
    }

    public function criadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criada_por_id');
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class);
    }

    public function movimentos(): HasMany
    {
        return $this->hasMany(MovimentoFinanceiro::class);
    }

    public function centavosPagos(): int
    {
        return $this->pagamentos()->get()->sum(fn (Pagamento $p) => Dinheiro::centavos($p->valor_pago));
    }

    public function totalPago(): float
    {
        return Dinheiro::reais($this->centavosPagos());
    }

    /** Fechada ou cancelada não tem saldo a receber. */
    public function saldoAberto(): float
    {
        return $this->status === 'aberta'
            ? Dinheiro::reais(Dinheiro::centavos($this->valor_total) - $this->centavosPagos())
            : 0.0;
    }

    /** A lista de confirmados da partida não é mais a que está nas parcelas em aberto. */
    public function rateioDesatualizado(): bool
    {
        if (! $this->rateada || $this->status !== 'aberta' || ! $this->partida) {
            return false;
        }

        $confirmados = $this->partida->confirmados()->pluck('user_id');
        $parcelas = $this->pagamentos()->get();

        return $confirmados->diff($parcelas->pluck('user_id'))->isNotEmpty()
            || $parcelas->where('status', 'pendente')->pluck('user_id')->diff($confirmados)->isNotEmpty();
    }
}
