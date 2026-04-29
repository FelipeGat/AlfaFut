<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mensagem extends Model
{
    protected $table = 'mensagens';

    protected $fillable = [
        'patota_id',
        'partida_id',
        'autor_id',
        'conteudo',
        'tipo',
        'fixada',
    ];

    protected $casts = [
        'fixada' => 'boolean',
    ];

    public function patota(): BelongsTo
    {
        return $this->belongsTo(Patota::class);
    }

    public function partida(): BelongsTo
    {
        return $this->belongsTo(Partida::class);
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
