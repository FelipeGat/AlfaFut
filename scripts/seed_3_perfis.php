<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$contas = [
    [
        'name' => 'Joao Jogador',
        'apelido' => 'Joao',
        'email' => 'jogador@teste.com',
        'password' => Hash::make('senha1234'),
        'tipo_usuario' => 'jogador',
        'email_verified_at' => now(),
    ],
    [
        'name' => 'Maria Organizadora',
        'apelido' => 'Mari',
        'email' => 'pelada@teste.com',
        'password' => Hash::make('senha1234'),
        'tipo_usuario' => 'dono_pelada',
        'email_verified_at' => now(),
    ],
    [
        'name' => 'Pedro Dono',
        'apelido' => 'Pedrao',
        'email' => 'campo@teste.com',
        'password' => Hash::make('senha1234'),
        'tipo_usuario' => 'dono_campo',
        'email_verified_at' => now(),
    ],
];

foreach ($contas as $c) {
    $u = User::updateOrCreate(['email' => $c['email']], $c);
    printf("OK  %-25s tipo=%-12s id=%d\n", $u->email, $u->tipo_usuario, $u->id);
}
