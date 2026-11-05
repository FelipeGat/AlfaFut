<?php

namespace App\Support;

/** Dinheiro é calculado em centavos inteiros; decimal só na borda (coluna, formulário e API). */
final class Dinheiro
{
    public static function centavos(string|int|float|null $valor): int
    {
        if ($valor === null || $valor === '') {
            return 0;
        }

        [$reais, $fracao] = array_pad(explode('.', (string) $valor, 2), 2, '');

        return (int) $reais * 100 + (int) str_pad(substr($fracao, 0, 2), 2, '0');
    }

    /** @return list<int> as parcelas, somando o total; os centavos restantes ficam com as primeiras */
    public static function dividir(int $centavos, int $partes): array
    {
        if ($partes < 1) {
            return [];
        }

        $base = intdiv($centavos, $partes);
        $resto = $centavos % $partes;
        $parcelas = [];

        for ($i = 0; $i < $partes; $i++) {
            $parcelas[] = $base + ($i < $resto ? 1 : 0);
        }

        return $parcelas;
    }

    public static function decimal(int $centavos): string
    {
        return sprintf('%d.%02d', intdiv($centavos, 100), $centavos % 100);
    }

    public static function reais(int $centavos): float
    {
        return $centavos / 100;
    }

    public static function formatar(int $centavos): string
    {
        return 'R$ '.number_format(intdiv($centavos, 100), 0, ',', '.').sprintf(',%02d', $centavos % 100);
    }
}
