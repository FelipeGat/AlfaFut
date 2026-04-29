<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartidaConfirmacao extends Model
{
    protected $table = 'partida_confirmacoes';

    protected $fillable = [
        'partida_id',
        'user_id',
        'status',
        'em_lista_espera',
        'posicao_lista_espera',
        'observacao',
        'confirmado_em',
    ];

    protected $casts = [
        'em_lista_espera' => 'boolean',
        'confirmado_em' => 'datetime',
    ];

    public function partida(): BelongsTo
    {
        return $this->belongsTo(Partida::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
