<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Quem fez o quê em despesa e parcela. Só de inclusão. */
class MovimentoFinanceiro extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'movimentos_financeiros';

    protected $fillable = [
        'patota_id',
        'despesa_id',
        'pagamento_id',
        'user_id',
        'acao',
        'descricao',
        'valor_centavos',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
