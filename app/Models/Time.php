<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Time extends Model
{
    protected $fillable = [
        'partida_id',
        'nome',
        'cor',
        'gols',
        'brasao',
        'clube_codigo',
    ];

    public function partida(): BelongsTo
    {
        return $this->belongsTo(Partida::class);
    }

    public function jogadores(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'time_jogadores')
            ->withPivot(['posicao', 'gols', 'assistencias'])
            ->withTimestamps();
    }

    public function escalacao(): HasMany
    {
        return $this->hasMany(TimeJogador::class);
    }
}
