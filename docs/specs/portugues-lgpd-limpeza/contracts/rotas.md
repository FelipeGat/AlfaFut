# Rotas: Português, LGPD e limpeza

## Novas `[PROPOSTA — a validar na implementacao]`

| Método | Rota | Acesso | Resultado |
|--------|------|--------|-----------|
| GET | `/privacidade` | público | Política de privacidade |
| GET | `/termos` | público | Termos de uso |
| GET | `/perfil/meus-dados` | login | Download JSON dos próprios dados |
| GET | `/api/v1/perfil/meus-dados` | token | Download JSON dos próprios dados; não exige e-mail confirmado |

## Alteradas

| Rota | Mudança |
|------|---------|
| `POST /forgot-password` | Mesma mensagem para qualquer e-mail |
| `POST /api/v1/auth/esqueci-senha` | Mesma mensagem e 200 para qualquer e-mail válido |
| `POST /reset-password` | Mesma mensagem de erro para token errado e conta inexistente; encerra todos os acessos |
| `PUT /password` | Encerra os tokens e as outras sessões |
| `POST /register` | Exige `aceite_termos` |
| `POST /api/v1/auth/registrar` | Exige `aceite_termos`; aceita `tipo_usuario` opcional (`jogador`, `dono_pelada`, `dono_campo`). **Muda o aplicativo:** sem o aceite, responde 422 |
| `GET /api/v1/auth/eu` | Ganha `tipo_usuario` |
| `POST /api/v1/patotas` e demais respostas de turma | `total_membros` volta a sair |
| Toda a API | Erros sempre em JSON, sem redirecionar; 401, 404, 405 e 500 em português |

## Formato do arquivo baixado `[PROPOSTA — a validar na implementacao]`

Chaves de primeiro nível: `gerado_em`, `cadastro`, `acessibilidade`, `turmas`, `confirmacoes`, `parcelas`, `mensagens`, `campos`.
