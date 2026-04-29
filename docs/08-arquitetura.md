# 08 - Arquitetura e diagramas

> Etapa "n" do projeto: especificacao e analise (UML, casos de uso, diagrama de atividades).

## Visao geral

```
+----------------------+         HTTPS         +---------------------+
|                      |  <----------------->  |                     |
|   AlfaFutApp         |    JSON / Sanctum     |   AlfaFut (Laravel) |
|   Flutter            |                       |   PHP 8.2 + SQLite  |
|   (Android / iOS)    |                       |                     |
+----------------------+                       +----------+----------+
                                                          |
+----------------------+         HTTPS                    |
|                      |  ----------------->              |
|   Web Browser        |    Blade / Cookie session        |
|   (qualquer SO)      |                                  |
+----------------------+                                  |
                                                          v
                                                   +-------------+
                                                   |  Banco de   |
                                                   |  dados      |
                                                   |  (SQLite)   |
                                                   +-------------+
```

## Camadas (backend Laravel)

```
+--------------------------------+
| routes/web.php  routes/api.php |
+--------------------------------+
| Controllers (Web/, Api/)       |
+--------------------------------+
| Models (Eloquent)              |  Resources (transformers)
+--------------------------------+
| Migrations (database schema)   |
+--------------------------------+
```

## Camadas (Flutter - Clean simplificada)

```
features/<modulo>/
  data/
    <modelo>.dart            <- entidades (Usuario, Patota, Partida)
    <modulo>_repository.dart <- chamadas HTTP via Dio
  presentation/
    <pagina>_page.dart       <- widgets + ConsumerState
    <componente>.dart        <- widgets reutilizaveis

core/
  config.dart           <- baseUrl da API
  providers.dart        <- providers Riverpod globais
  network/api_client.dart   <- Dio + interceptor de token
  storage/token_storage.dart <- secure storage
  theme/app_theme.dart  <- Material Design 3 + alto contraste
  router/app_router.dart <- go_router + redirect por auth
```

## Modelo de dados (ER simplificado)

```
users (1) -----< patota_membros >----- (N) patotas
                                        |
                                        |--< partidas
                                        |     |
                                        |     |--< partida_confirmacoes
                                        |     |--< times --< time_jogadores
                                        |     |--< despesas --< pagamentos
                                        |
                                        |--< locais
                                        |--< mensagens
```

### Tabelas e suas chaves

| Tabela | Chaves estrangeiras |
|--------|---------------------|
| `patotas` | `criador_id` -> users |
| `patota_membros` | `patota_id`, `user_id` |
| `locais` | `patota_id` (nullable) |
| `partidas` | `patota_id`, `local_id` (nullable), `organizador_id` |
| `partida_confirmacoes` | `partida_id`, `user_id` |
| `times` | `partida_id` |
| `time_jogadores` | `time_id`, `user_id` |
| `despesas` | `patota_id`, `partida_id` (nullable), `criada_por_id` |
| `pagamentos` | `despesa_id`, `user_id` |
| `mensagens` | `patota_id`, `partida_id` (nullable), `autor_id` |

## Diagrama de casos de uso (UML)

```
                                     +-----------------+
                                     |     Membro      |
                                     +--------+--------+
                                              |
                +-----------------------------+-----------------------------+
                |                             |                             |
        +-------v--------+          +---------v--------+         +----------v---------+
        | Confirmar      |          | Ver mural da    |         | Configurar         |
        | presenca       |          | patota          |         | acessibilidade     |
        +----------------+          +-----------------+         +--------------------+

                +-----------------+
                | Administrador   | (estende Membro)
                +--------+--------+
                         |
        +----------------+----------------+--------------------+
        |                |                |                    |
+-------v--------+ +-----v---------+ +----v-----------+ +------v--------+
| Criar patota   | | Criar         | | Lancar         | | Ratear despesa |
|                | | partida       | | despesa        | |                |
+----------------+ +---------------+ +----------------+ +----------------+
```

## Diagrama de atividades - Confirmar presenca

```
   [Inicio]
      |
      v
   < Usuario abre detalhe da partida >
      |
      v
   < Toca em "Confirmar presenca" >
      |
      v
   < Sistema verifica vagas >
      |
      |---- (ha vaga) ---->  [ Cria PartidaConfirmacao com em_lista_espera=false ]
      |                                 |
      |                                 v
      |                      [ Notifica "Presenca confirmada!" ]
      |
      |---- (cheia) ----->   [ Cria PartidaConfirmacao com em_lista_espera=true,
      |                         posicao = max(posicao_atual)+1 ]
      |                                 |
      |                                 v
      |                      [ Notifica "Voce entrou na lista de espera" ]
      |
      v
   [Fim]
```

## Diagrama de atividades - Promocao da lista de espera

```
   [ Usuario cancela confirmacao ]
              |
              v
   < Sistema verifica se ha alguem na lista de espera >
              |
       (sim)  |  (nao)
       v          v
   < Pega proximo  > [Fim]
   < ordenado      >
              |
              v
   < Atualiza para em_lista_espera=false, posicao_lista_espera=null >
              |
              v
   < Notifica usuario promovido (push/email - planejado) >
              |
              v
            [Fim]
```

## Decisoes arquiteturais

### Por que SQLite em desenvolvimento?
Zero configuracao, arquivo unico, ideal para projeto de extensao. Em producao basta trocar `DB_CONNECTION=mysql` no `.env`.

### Por que Sanctum?
Token simples baseado em DB, ideal para SPA mobile. Sem complexidade de OAuth para escopo do projeto.

### Por que Flutter Riverpod (e nao Provider/Bloc)?
Mesma stack do `AlfaHomeApp` (consistencia entre projetos do usuario), suporte oficial a `family` e `autoDispose`, sem boilerplate de Bloc.

### Por que slug + codigo de convite?
- Slug: URLs legiveis (`/patotas/patota-do-felipe`) - SEO + memorabilidade.
- Codigo de convite: 8 chars uppercase, facil de ditar/digitar - onboarding sem fricao.
