<?php
/**
 * Copia dados de database/database.sqlite para a conexao 'mysql' padrao do Laravel.
 *
 * Uso:
 *   php scripts/sqlite_to_mysql.php
 *
 * Pre-requisito: 'php artisan migrate:fresh' ja rodou no MySQL,
 * deixando o schema vazio e pronto pra receber as linhas.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$sqlitePath = __DIR__ . '/../database/database.sqlite';
if (! file_exists($sqlitePath)) {
    fwrite(STDERR, "SQLite nao encontrado em {$sqlitePath}\n");
    exit(1);
}

// Conexao manual ao SQLite (independente do .env atual)
config(['database.connections.legacy_sqlite' => [
    'driver' => 'sqlite',
    'database' => $sqlitePath,
    'prefix' => '',
    'foreign_key_constraints' => false,
]]);

// Ordem importa: tabelas pai antes das filhas.
$ordem = [
    'users',
    'patotas',
    'locais',
    'patota_membros',
    'partidas',
    'partida_confirmacoes',
    'times',
    'time_jogadores',
    'eventos_partida',
    'mensagens',
    'despesas',
    'pagamentos',
    'personal_access_tokens',
    'cache',
    'cache_locks',
    'sessions',
    'jobs',
    'job_batches',
    'failed_jobs',
    'password_reset_tokens',
];

echo "Copiando " . count($ordem) . " tabelas...\n\n";

DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0');

$totalLinhas = 0;
foreach ($ordem as $tabela) {
    if (! Schema::connection('legacy_sqlite')->hasTable($tabela)) {
        printf("  %-30s [pula - nao existe no SQLite]\n", $tabela);
        continue;
    }
    if (! Schema::connection('mysql')->hasTable($tabela)) {
        printf("  %-30s [pula - nao existe no MySQL]\n", $tabela);
        continue;
    }

    DB::connection('mysql')->table($tabela)->truncate();

    $rows = DB::connection('legacy_sqlite')->table($tabela)->get()->map(fn($r) => (array) $r)->all();
    if (empty($rows)) {
        printf("  %-30s 0\n", $tabela);
        continue;
    }

    // Normaliza booleans SQLite (0/1) — MySQL aceita ambos
    foreach ($rows as &$linha) {
        foreach ($linha as $k => $v) {
            if (is_string($v) && (str_starts_with($v, '{') || str_starts_with($v, '['))) {
                // mantem JSON como string
            }
        }
    }
    unset($linha);

    foreach (array_chunk($rows, 200) as $lote) {
        DB::connection('mysql')->table($tabela)->insert($lote);
    }

    $totalLinhas += count($rows);
    printf("  %-30s %d\n", $tabela, count($rows));
}

DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1');

echo "\nOK - {$totalLinhas} linhas copiadas.\n";
