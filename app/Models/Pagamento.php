<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pagamento extends Model
{
    protected $fillable = [
        'despesa_id',
        'user_id',
        'valor_devido',
        'valor_pago',
        'data_vencimento',
        'data_pagamento',
        'forma_pagamento',
        'status',
        'observacao',
    ];

    protected $casts = [
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
        'valor_devido' => 'decimal:2',
        'valor_pago' => 'decimal:2',
    ];

    public function despesa(): BelongsTo
    {
        return $this->belongsTo(Despesa::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
