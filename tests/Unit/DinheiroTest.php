<?php

namespace Tests\Unit;

use App\Support\Dinheiro;
use PHPUnit\Framework\TestCase;

class DinheiroTest extends TestCase
{
    public function test_converte_sem_ponto_flutuante(): void
    {
        $this->assertSame(1, Dinheiro::centavos('0.01'));
        $this->assertSame(10, Dinheiro::centavos('0.1'));
        $this->assertSame(1999, Dinheiro::centavos('19.99'));
        $this->assertSame(1999, Dinheiro::centavos(19.99));
        $this->assertSame(1000, Dinheiro::centavos(10));
        $this->assertSame(115, Dinheiro::centavos('1.15'));
        $this->assertSame(433, Dinheiro::centavos(4.33));
        $this->assertSame(99999999, Dinheiro::centavos('999999.99'));
        $this->assertSame(0, Dinheiro::centavos(null));
    }

    public function test_divide_e_a_soma_fecha(): void
    {
        $this->assertSame([3334, 3333, 3333], Dinheiro::dividir(10000, 3));
        $this->assertSame([1, 0, 0], Dinheiro::dividir(1, 3));
        $this->assertSame([], Dinheiro::dividir(100, 0));

        foreach ([1, 7, 99, 100, 12345, 99999999] as $total) {
            foreach (range(1, 13) as $partes) {
                $parcelas = Dinheiro::dividir($total, $partes);
                $this->assertSame($total, array_sum($parcelas));
                $this->assertLessThanOrEqual(1, max($parcelas) - min($parcelas));
            }
        }
    }

    public function test_formata(): void
    {
        $this->assertSame('R$ 1.234,50', Dinheiro::formatar(123450));
        $this->assertSame('33.34', Dinheiro::decimal(3334));
        $this->assertSame(33.34, Dinheiro::reais(3334));
    }
}
