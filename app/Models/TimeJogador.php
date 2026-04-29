<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeJogador extends Model
{
    protected $table = 'time_jogadores';

    protected $fillable = [
        'time_id',
        'user_id',
        'posicao',
        'gols',
        'assistencias',
    ];

    public function time(): BelongsTo
    {
        return $this->belongsTo(Time::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
