<?php

namespace App\Console\Commands;

use App\Models\EventoPartida;
use App\Models\Partida;
use App\Models\PartidaConfirmacao;
use App\Models\Patota;
use App\Models\User;
use App\Services\SorteioTimes;
use Illuminate\Console\Command;

class PartidaDemo extends Command
{
    protected $signature = 'partida:demo
        {--reset : Apagar tudo e recriar do zero}
        {--minutos=18 : Quantos minutos a partida ja esta em andamento}';

    protected $description = 'Cria uma partida de demo em andamento com times sorteados e gols ja registrados';

    public function handle(SorteioTimes $sorteio): int
    {
        $patota = Patota::firstOrFail();
        $partida = $patota->partidas()->orderByDesc('data_hora')->first();

        if (! $partida) {
            $this->error('Patota sem partidas. Rode o seeder primeiro: php artisan db:seed --class=PatotaSeeder');
            return 1;
        }

        $this->info("Patota: {$patota->nome} (id {$patota->id})");
        $this->info("Partida: {$partida->titulo} (id {$partida->id})");

        // Confirma todos os membros
        $this->info('Confirmando todos os membros...');
        foreach ($patota->membrosAtivos as $m) {
            PartidaConfirmacao::updateOrCreate(
                ['partida_id' => $partida->id, 'user_id' => $m->id],
                ['status' => 'confirmado', 'em_lista_espera' => false, 'confirmado_em' => now()]
            );
        }

        // Sorteia times (com brasao + nome de clube)
        $this->info('Sorteando times com brasoes...');
        $times = $sorteio->sortear($partida);
        foreach ($times as $t) {
            $this->line("  -> {$t->nome} ({$t->jogadores->count()} jogadores)");
        }

        // Reset de eventos antigos e placar
        $partida->eventos()->delete();
        foreach ($times as $t) $t->update(['gols' => 0]);

        // Marca partida iniciada N minutos atras
        $minutosAtras = (int) $this->option('minutos');
        $iniciadaEm = now()->subMinutes($minutosAtras);

        $partida->update([
            'iniciada_em' => $iniciadaEm,
            'pausada_em' => null,
            'finalizada_em' => null,
            'tempo_acumulado_segundos' => 0,
            'placar_a' => 0,
            'placar_b' => 0,
            'status' => 'em_andamento',
        ]);

        // Evento INICIO
        EventoPartida::create([
            'partida_id' => $partida->id,
            'tipo' => EventoPartida::TIPO_INICIO,
            'minuto' => 0,
            'registrado_por_id' => $patota->responsavel_id ?? $patota->criador_id,
        ]);

        // Marca alguns gols dramaticos
        $timeA = $times[0];
        $timeB = $times[1];
        $jogadoresA = $timeA->jogadores;
        $jogadoresB = $timeB->jogadores;

        $golsParaMarcar = [
            ['time' => $timeA, 'jogador' => $jogadoresA[0] ?? null, 'minuto' => 3],
            ['time' => $timeB, 'jogador' => $jogadoresB[0] ?? null, 'minuto' => 7],
            ['time' => $timeA, 'jogador' => $jogadoresA[1] ?? $jogadoresA[0] ?? null, 'minuto' => 11],
            ['time' => $timeA, 'jogador' => $jogadoresA[0] ?? null, 'minuto' => 15],
            ['time' => $timeB, 'jogador' => $jogadoresB[1] ?? $jogadoresB[0] ?? null, 'minuto' => 17],
        ];

        foreach ($golsParaMarcar as $gol) {
            if ($gol['minuto'] > $minutosAtras) continue;

            EventoPartida::create([
                'partida_id' => $partida->id,
                'time_id' => $gol['time']->id,
                'jogador_id' => $gol['jogador']?->id,
                'tipo' => EventoPartida::TIPO_GOL,
                'minuto' => $gol['minuto'],
                'registrado_por_id' => $patota->responsavel_id ?? $patota->criador_id,
                'created_at' => $iniciadaEm->copy()->addMinutes($gol['minuto']),
            ]);

            $gol['time']->increment('gols');
            $this->line("  ⚽ {$gol['minuto']}' - " . ($gol['jogador']?->apelido ?? 'desconhecido') . " (" . $gol['time']->nome . ")");
        }

        $partida->update([
            'placar_a' => $timeA->fresh()->gols,
            'placar_b' => $timeB->fresh()->gols,
        ]);

        $this->newLine();
        $this->info("✅ Partida demo pronta!");
        $this->info("Placar atual: {$timeA->fresh()->gols} x {$timeB->fresh()->gols} ({$timeA->nome} vs {$timeB->nome})");
        $this->newLine();
        $this->line("Acesse no navegador (logado como admin@alfafut.test / senha1234):");
        $this->line("  TV (projete na televisao):    http://127.0.0.1:8000/partidas/{$partida->id}/tv");
        $this->line("  Controle (responsavel):       http://127.0.0.1:8000/partidas/{$partida->id}/controle");

        return 0;
    }
}
