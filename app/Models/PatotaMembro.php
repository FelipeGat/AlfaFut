<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatotaMembro extends Model
{
    protected $table = 'patota_membros';

    protected $fillable = [
        'patota_id',
        'user_id',
        'papel',
        'status',
        'entrou_em',
        'saiu_em',
    ];

    protected $casts = [
        'entrou_em' => 'datetime',
        'saiu_em' => 'datetime',
    ];

    public function patota(): BelongsTo
    {
        return $this->belongsTo(Patota::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isAdmin(): bool
    {
        return in_array($this->papel, ['administrador', 'organizador']);
    }
}
