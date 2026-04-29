# 06 - Prototipo de baixa fidelidade (Apendice D)

> Etapa "k" do projeto: prototipacao de baixa fidelidade.
>
> Foco em **funcionalidades**, sem preocupacao estetica. Wireframes em ASCII representando as telas principais.

## Tela 01 - Login

```
+------------------------------------------+
|                                          |
|            [ logo AlfaFut ]              |
|                                          |
|       Sua patota organizada e            |
|              acessivel                   |
|                                          |
|  +------------------------------------+  |
|  | E-mail                             |  |
|  +------------------------------------+  |
|                                          |
|  +------------------------------------+  |
|  | Senha                       [olho] |  |
|  +------------------------------------+  |
|                                          |
|  +------------------------------------+  |
|  |             ENTRAR                 |  |
|  +------------------------------------+  |
|                                          |
|       Nao tenho conta - Criar agora      |
|                                          |
+------------------------------------------+
```
**Funcionalidade:** Autentica via email/senha, abre dashboard.

---

## Tela 02 - Dashboard

```
+------------------------------------------+
| AlfaFut         Acessibilidade  [sair]   |
+------------------------------------------+
| Ola, Felipe!                             |
| Pronto para a proxima pelada?            |
+------------------------------------------+
|                                          |
| +------+  +------+  +------+             |
| | 2    |  | 3    |  | 1    |             |
| | pat. |  | prox.|  | conf.|             |
| +------+  +------+  +------+             |
|                                          |
| Minhas patotas             [+Nova]       |
| ----------------------------------       |
| > Patota do Felipe (15 membros)          |
| > Time da quadra (8 membros)             |
|                                          |
| Proximas partidas                        |
| ----------------------------------       |
| > Pelada de sabado                       |
|   Patota do Felipe - Sab 15:00           |
|   Arena Boa Vista - 2 vagas              |
|                                          |
| > Treino de quarta                       |
|   Patota do Felipe - Qua 19:00           |
|   Arena Boa Vista - 0 vagas (espera)     |
|                                          |
+------------------------------------------+
```

**Funcionalidades:**
- 3 cards com numeros importantes
- Lista de patotas com link para detalhe
- Proximas partidas com link para confirmar

---

## Tela 03 - Detalhe da partida

```
+------------------------------------------+
| <-  Pelada de sabado                     |
+------------------------------------------+
|                                          |
| Patota do Felipe                         |
| Sabado, 02 de maio as 15:00              |
|                                          |
| Detalhes:                                |
| - Local: Arena Boa Vista                 |
| - Endereco: Rua das Palmeiras, 1500      |
| - [icone] Local acessivel                |
| - Duracao: 90 min                        |
| - Valor: R$ 18,00                        |
| - Confirmar ate: 01/05 18:00             |
|                                          |
| +------------------------------------+   |
| |       CONFIRMAR PRESENCA           |   |
| +------------------------------------+   |
| +------------------------------------+   |
| |            NAO VOU                 |   |
| +------------------------------------+   |
|                                          |
| Confirmados (8/10)                       |
| ----------------------------------       |
| (CO) Carlos - goleiro                    |
| (AP) Ana Paula - atacante                |
| (BS) Bruno - zagueiro                    |
| (DS) Diego - meia                        |
| ...                                      |
|                                          |
| Lista de espera                          |
| 1. Marcelo                               |
|                                          |
+------------------------------------------+
```

**Funcionalidades:**
- Botao 1-toque para confirmar/recusar
- Lista de confirmados com posicao
- Lista de espera ordenada
- Indicador de acessibilidade do local

---

## Tela 04 - Nova partida (admin)

```
+------------------------------------------+
| <-  Nova partida                         |
+------------------------------------------+
|                                          |
| Titulo *                                 |
| +------------------------------------+   |
| |                                    |   |
| +------------------------------------+   |
|                                          |
| Local                                    |
| +------------------------------------+   |
| | [v] Selecionar local               |   |
| +------------------------------------+   |
|                                          |
| Data e hora *                            |
| +------------------------------------+   |
| | DD/MM/YYYY HH:MM                   |   |
| +------------------------------------+   |
|                                          |
| Duracao (min)            Vagas           |
| +-------+               +-------+        |
| |  90   |               |  10   |        |
| +-------+               +-------+        |
|                                          |
| Valor por jogador (R$)                   |
| +-------+                                |
| | 18,00 |                                |
| +-------+                                |
|                                          |
| Confirmar ate                            |
| +------------------------------------+   |
| | DD/MM/YYYY HH:MM                   |   |
| +------------------------------------+   |
|                                          |
| [ ] Lista de espera habilitada           |
|                                          |
| +------------------------------------+   |
| |            CRIAR PARTIDA           |   |
| +------------------------------------+   |
+------------------------------------------+
```

---

## Tela 05 - Despesas

```
+------------------------------------------+
| <-  Despesas - Patota do Felipe          |
+------------------------------------------+
| [+] Nova despesa                         |
|                                          |
| Aluguel do campo - sabado                |
| R$ 180,00 - 02/05/2026                   |
| Pago: R$ 36,00 / Saldo: R$ 144,00        |
| > Ver detalhes                           |
|                                          |
| Bolas novas                              |
| R$ 90,00 - 28/04/2026                    |
| Pago: R$ 90,00 / Saldo: R$ 0,00 [PAGO]   |
| > Ver detalhes                           |
+------------------------------------------+
```

---

## Tela 06 - Acessibilidade

```
+------------------------------------------+
| <-  Preferencias de acessibilidade       |
+------------------------------------------+
|                                          |
| VISUALIZACAO                             |
|                                          |
| [x] Alto contraste                       |
|     Tema preto e amarelo (WCAG 1.4.6)    |
|                                          |
| Tamanho da fonte                         |
| ( ) Pequena   (x) Media                  |
| ( ) Grande    ( ) Extra grande           |
|                                          |
| MOVIMENTO E LEITOR DE TELA               |
|                                          |
| [x] Reduzir animacoes                    |
| [ ] Otimizar para leitor de tela         |
|                                          |
| NECESSIDADES ESPECIFICAS                 |
|                                          |
| [ ] Cadeirante / mobilidade reduzida     |
| [ ] Baixa visao                          |
| [ ] Cego                                 |
| [ ] Surdo / deficiencia auditiva         |
| [ ] Comunicacao em Libras                |
| [ ] Deficiencia cognitiva                |
| [ ] Transtorno do espectro autista       |
|                                          |
| +------------------------------------+   |
| |        SALVAR PREFERENCIAS         |   |
| +------------------------------------+   |
+------------------------------------------+
```

**Funcionalidade chave:** as escolhas se aplicam imediatamente em toda a aplicacao (web e mobile).

---

## Validacoes esperadas com usuarios da comunidade

Conforme metodologia (5 validacoes a cada 20% da prototipacao):

1. **20%** - login, cadastro, dashboard - Validar termo "patota" e fluxo de codigo de convite.
2. **40%** - detalhe de partida e confirmacao - Validar lista de espera e fluxo 1-toque.
3. **60%** - despesas e rateio - Validar se o conceito de "ratear" esta claro.
4. **80%** - acessibilidade - Testar com Ana Paula (persona surda).
5. **100%** - mural e mensagens - Validar com grupo completo da patota.

Para a etapa de validacao, **deve ser usado o app rodando em modo prototipo** (banco SQLite com dados ficticios via `php artisan db:seed`) sobre a interface real ja construida.
