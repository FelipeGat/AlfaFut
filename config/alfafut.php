<?php

return [
    // Versão do texto de /privacidade e /termos, guardada no aceite de cada cadastro.
    'versao_termos' => '2026-11-05',

    // Sem fonte no repositório: cada ambiente informa, e produção não sobe sem nome e contato.
    'responsavel' => [
        'nome' => env('ALFAFUT_RESPONSAVEL_NOME'),
        'documento' => env('ALFAFUT_RESPONSAVEL_DOCUMENTO'),
        'contato_privacidade' => env('ALFAFUT_CONTATO_PRIVACIDADE'),
    ],
];
