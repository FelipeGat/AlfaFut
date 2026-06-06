<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campo extends Model
{
    protected $table = 'campos';

    protected $fillable = [
        'dono_id',
        'nome',
        'endereco',
        'cidade',
        'estado',
        'cep',
        'tipo_piso',
        'coberto',
        'possui_vestiario',
        'possui_estacionamento',
        'acessivel_cadeirante',
        'valor_hora',
        'contato_whatsapp',
        'descricao',
        'foto_url',
        'ativo',
    ];

    protected $casts = [
        'coberto' => 'boolean',
        'possui_vestiario' => 'boolean',
        'possui_estacionamento' => 'boolean',
        'acessivel_cadeirante' => 'boolean',
        'ativo' => 'boolean',
        'valor_hora' => 'decimal:2',
    ];

    public function dono(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dono_id');
    }

    public function partidas(): HasMany
    {
        return $this->hasMany(Partida::class, 'campo_id');
    }

    public function scopeAtivos(Builder $q): Builder
    {
        return $q->where('ativo', true);
    }

    public static function tiposPiso(): array
    {
        return [
            'grama_natural' => 'Grama natural',
            'grama_sintetica' => 'Grama sintetica',
            'saibro' => 'Saibro',
            'quadra_coberta' => 'Quadra coberta',
            'futsal' => 'Futsal',
        ];
    }
}
