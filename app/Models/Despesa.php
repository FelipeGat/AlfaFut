<?php

namespace App\Models;

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

    public function patota(): BelongsTo
    {
        return $this->belongsTo(Patota::class);
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

    public function totalPago(): float
    {
        return (float) $this->pagamentos()->sum('valor_pago');
    }

    public function saldoAberto(): float
    {
        return (float) $this->valor_total - $this->totalPago();
    }
}
