# 03 - Modelo 3C de Colaboracao

> Etapa "c" do projeto: aplicacao do Modelo 3C (Fuks et al., 2005).

O Modelo 3C define que sistemas colaborativos se sustentam em tres pilares interdependentes: **Comunicacao**, **Coordenacao** e **Cooperacao**, mediados por **Mecanismos de Percepcao**.

```
            COMUNICACAO
                |
                v
   COORDENACAO <----> COOPERACAO
                |
                v
        Mecanismos de Percepcao
```

## Mapeamento das funcionalidades do AlfaFut

### Comunicacao
Onde os membros trocam mensagens e informacoes:

| Funcionalidade | Componente | Tabela |
|----------------|------------|--------|
| Mural da patota | Lista de mensagens fixadas + recentes | `mensagens` |
| Mensagens por partida | Comentarios por evento | `mensagens.partida_id` |
| Notificacoes | Avisos de nova partida, despesa, mensagem | (planejado) |
| Codigo de convite | Mensagem assincrona "voce esta convidado" | `patotas.codigo_convite` |

### Coordenacao
Como as atividades sao planejadas e organizadas:

| Funcionalidade | Componente | Tabela |
|----------------|------------|--------|
| Agendamento de partida | Form com data, hora, local, vagas | `partidas` |
| Confirmacao de presenca | Botao 1-toque por membro | `partida_confirmacoes` |
| Lista de espera ordenada | Promove proximo quando alguem desiste | `partida_confirmacoes.posicao_lista_espera` |
| Formacao de times | Balanceamento por nivel/posicao | `times` + `time_jogadores` |
| Definicao de papeis | Administrador, organizador, membro | `patota_membros.papel` |

### Cooperacao
Como o grupo trabalha em conjunto para um objetivo:

| Funcionalidade | Componente | Tabela |
|----------------|------------|--------|
| Lancamento de despesas | Aluguel do campo, arbitragem etc. | `despesas` |
| Rateio automatico | Dividir despesa entre confirmados | `pagamentos` |
| Registro de pagamento | Cada membro registra seu PIX/dinheiro | `pagamentos.status` |
| Estatisticas de partida | Gols, assistencias por jogador | `time_jogadores.gols/assistencias` |

### Mecanismos de Percepcao
Como o usuario percebe o estado do grupo:

| Mecanismo | Implementacao |
|-----------|---------------|
| "Quem confirmou?" | Lista de avatares na pagina da partida |
| "Estou confirmado?" | Chip visivel "Confirmado / Espera / Recusado" |
| "Ha vagas?" | Indicador "8/10 vagas" sempre visivel |
| "Quem deve?" | Lista de pagamentos pendentes |
| "Qual minha posicao na fila?" | Texto explicito "posicao 2 da lista de espera" |

## Por que 3C?

A pesquisa (Costa, 2018; Fuks et al., 2005) mostra que sistemas que cobrem apenas comunicacao (como WhatsApp) deixam buracos em coordenacao (quem vai? quanto custa?) e cooperacao (quem pagou?). O AlfaFut foi desenhado para cobrir os tres pilares de forma integrada.

## Referencias usadas
- COSTA, S. E. da. iLibras como facilitador na comunicacao efetiva do surdo. 2018.
- FUKS, H. et al. Applying The 3C Model to Groupware Development. International Journal of Cooperative Information Systems, 2005.
- SANTOS, L. V. CAMISA10: aplicativo colaborativo para gerenciamento de Patotas. UFSC, 2022.
- ZUCCHI, D. KEVIN: Formador de grupos em praticas esportivas. FURB, 2018.
