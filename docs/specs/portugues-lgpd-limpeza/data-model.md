# Data Model: Português, LGPD e limpeza

## Entity: User (campos novos)

| Campo | Tipo | Regra |
|-------|------|-------|
| `termos_aceitos_em` | timestamp, nulo | Preenchido só no cadastro; fora do `$fillable` |
| `termos_versao` | texto até 20, nulo | Versão de `config('alfafut.versao_termos')`; fora do `$fillable` |

Conta criada por `alfafut:criar-admin` ou por seed pode ficar sem aceite: não passou pelo cadastro público.

`role` e `tipo_usuario` saem do `$fillable`. Os fluxos que os definem usam `forceFill` de forma explícita.

## Entity: Acesso pela API

Sem coluna nova. A validade é calculada pelo Sanctum a partir de `created_at`.

## Sem tabela nova

O arquivo dos próprios dados é montado na hora, sem guardar cópia.
