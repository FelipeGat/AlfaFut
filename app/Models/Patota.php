<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Patota extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nome',
        'slug',
        'descricao',
        'logo_url',
        'cidade',
        'estado',
        'criador_id',
        'jogadores_por_time',
        'quantidade_times',
        'valor_mensalidade',
        'publica',
        'codigo_convite',
    ];

    protected $casts = [
        'publica' => 'boolean',
        'valor_mensalidade' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Patota $patota) {
            if (empty($patota->slug)) {
                $patota->slug = Str::slug($patota->nome) . '-' . Str::random(5);
            }
            if (empty($patota->codigo_convite)) {
                $patota->codigo_convite = strtoupper(Str::random(8));
            }
        });
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criador_id');
    }

    public function membros(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'patota_membros')
            ->withPivot(['papel', 'status', 'entrou_em', 'saiu_em'])
            ->withTimestamps();
    }

    public function membrosAtivos(): BelongsToMany
    {
        return $this->membros()->wherePivot('status', 'ativo');
    }

    public function partidas(): HasMany
    {
        return $this->hasMany(Partida::class);
    }

    public function locais(): HasMany
    {
        return $this->hasMany(Local::class);
    }

    public function despesas(): HasMany
    {
        return $this->hasMany(Despesa::class);
    }

    public function mensagens(): HasMany
    {
        return $this->hasMany(Mensagem::class);
    }

    public function vagasPorPartida(): int
    {
        return $this->jogadores_por_time * $this->quantidade_times;
    }
}
