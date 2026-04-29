<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventoPartida extends Model
{
    protected $table = 'eventos_partida';

    protected $fillable = [
        'partida_id',
        'time_id',
        'jogador_id',
        'assistencia_id',
        'tipo',
        'minuto',
        'observacao',
        'registrado_por_id',
    ];

    public const TIPO_INICIO = 'inicio';
    public const TIPO_GOL = 'gol';
    public const TIPO_GOL_CONTRA = 'gol_contra';
    public const TIPO_PAUSA = 'pausa';
    public const TIPO_RETOMADA = 'retomada';
    public const TIPO_FIM = 'fim';

    public function partida(): BelongsTo
    {
        return $this->belongsTo(Partida::class);
    }

    public function time(): BelongsTo
    {
        return $this->belongsTo(Time::class);
    }

    public function jogador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'jogador_id');
    }

    public function assistencia(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assistencia_id');
    }
}
