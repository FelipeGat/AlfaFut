<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Local extends Model
{
    protected $table = 'locais';

    protected $fillable = [
        'patota_id',
        'nome',
        'endereco',
        'cidade',
        'estado',
        'cep',
        'latitude',
        'longitude',
        'tipo_piso',
        'coberto',
        'possui_vestiario',
        'possui_estacionamento',
        'acessivel_cadeirante',
        'valor_locacao',
        'contato',
        'observacoes',
    ];

    protected $casts = [
        'coberto' => 'boolean',
        'possui_vestiario' => 'boolean',
        'possui_estacionamento' => 'boolean',
        'acessivel_cadeirante' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'valor_locacao' => 'decimal:2',
    ];

    public function patota(): BelongsTo
    {
        return $this->belongsTo(Patota::class);
    }

    public function partidas(): HasMany
    {
        return $this->hasMany(Partida::class);
    }
}
