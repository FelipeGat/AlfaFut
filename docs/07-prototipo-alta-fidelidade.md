# 07 - Prototipo de alta fidelidade (Apendice E)

> Etapa "l" do projeto: prototipacao de alta fidelidade.
>
> O **proprio sistema implementado** funciona como prototipo de alta fidelidade, ja seguindo padroes do **Material Design 3**, **Heuristicas de Nielsen** e diretrizes **WCAG 2.1**.

## Por que o codigo e o prototipo

Diferente da abordagem tradicional de criar telas em Figma e depois implementar, optamos pela **estrategia de implementacao iterativa**: o prototipo de alta fidelidade ja e o produto. Isso e justificado por:

1. **Custo de manutencao zero entre prototipo e producao** - alteracoes validadas com usuario sao imediatas.
2. **Alta fidelidade real** - usuarios interagem com o software de verdade, nao com simulacao.
3. **Validacao de acessibilidade autentica** - Material Design e WCAG so funcionam de verdade no codigo, nao em mockups Figma.

A literatura moderna (Tera, 2020; Francisco, 2021) ja considera essa abordagem valida, especialmente para projetos colaborativos.

## Tokens de design implementados

### Cores (Material Design 3)
| Token | Valor | Uso |
|-------|-------|-----|
| `--md-sys-color-primary` | `#1B5E20` | Verde futebol - botoes primarios, headers |
| `--md-sys-color-on-primary` | `#FFFFFF` | Texto sobre primary |
| `--md-sys-color-primary-container` | `#B9F6CA` | Chips de "Confirmado", backgrounds suaves |
| `--md-sys-color-secondary` | `#F57C00` | Laranja - acoes secundarias, FAB |
| `--md-sys-color-error` | `#BA1A1A` | Mensagens de erro |
| `--md-sys-color-surface` | `#F7F9F7` | Background da pagina |
| `--md-sys-color-on-surface` | `#1A1C19` | Texto principal |

### Tipografia
- Familia: **Roboto** (via Bunny Fonts).
- Escalas: 14, 16, 18, 22 px (configuravel pelo usuario).
- Pesos: 400 (regular), 500 (medium), 700 (bold).

### Spacing
- Sistema 4px (Tailwind padrao).
- Cards: padding 24px (`p-6`).
- Botoes: min height 44px (WCAG 2.5.5).

### Radius
- Cards: 16px
- Botoes: stadium (totalmente arredondados)
- Inputs: 12px

## Componentes implementados

### Web (Blade + Tailwind)
- Layout principal com skip link, header, main, footer.
- Navigation com appbar verde + dropdown de usuario.
- Cards Material 3 com elevation 0 e bordas suaves.
- Buttons com variantes: primary, secondary, outline, danger.
- Inputs com filled style, label visivel acima.
- Chips de status (confirmado/espera/recusado).
- Tabs para "Proximas / Passadas".

### Mobile (Flutter Material 3)
- `MaterialApp.router` com `useMaterial3: true`.
- AppBar verde com FAB estendido.
- ListView com cards e separators.
- ChoiceChips para selecao de tamanho de fonte.
- SwitchListTile para toggles de acessibilidade.
- AlertDialog para entrada por codigo de convite.

## Telas - como acessar o prototipo de alta fidelidade

| Tela | URL Web | Rota Flutter |
|------|---------|--------------|
| Login | `/login` | `/login` |
| Dashboard | `/dashboard` | `/dashboard` |
| Lista de patotas | `/patotas` | `/dashboard` (mesmo) |
| Nova patota | `/patotas/create` | `/patotas/nova` |
| Detalhe patota | `/patotas/{slug}` | `/patotas/{id}` |
| Lista de partidas | `/partidas` | (no detalhe da patota) |
| Detalhe partida | `/partidas/{id}` | `/partidas/{id}` |
| Acessibilidade | `/acessibilidade` | `/acessibilidade` |

## Sequencia de validacao (5 ciclos a cada 20%)

| Ciclo | Telas a validar | Foco |
|-------|------------------|------|
| 1 | Login, registro, dashboard | Fluxo de entrada e primeira impressao |
| 2 | Criar patota, codigo de convite, entrar com codigo | Onboarding de novo membro |
| 3 | Detalhe partida, confirmar, lista de espera | Coordenacao - heart of the app |
| 4 | Despesas, rateio, pagamento | Cooperacao financeira |
| 5 | Acessibilidade, mural | Comunicacao + inclusao |

Em cada ciclo, registrar:
- Foto/video do usuario testando (com consentimento).
- Anotacoes de "tropecos" (confusao, pergunta, hesitacao > 3s).
- Sugestoes textuais.
- Pontuacao SUS (System Usability Scale) ao final.

## Como testar visualmente

1. Servidor rodando em `http://localhost/AlfaFut/public`.
2. Login: `admin@alfafut.test` / `senha1234`.
3. Acesse `/acessibilidade` e ative alto contraste para validar WCAG 1.4.6.
4. Use `Tab` para navegar - todo elemento deve receber foco visivel.
5. Use Lighthouse (Chrome DevTools) > meta de Acessibilidade >= 95.

## Capturas de tela

> As capturas devem ser geradas durante as validacoes de campo e adicionadas em `docs/screenshots/`.

Pastas sugeridas:
```
docs/screenshots/
  ciclo-01-onboarding/
  ciclo-02-patota/
  ciclo-03-partida/
  ciclo-04-financeiro/
  ciclo-05-acessibilidade/
```
