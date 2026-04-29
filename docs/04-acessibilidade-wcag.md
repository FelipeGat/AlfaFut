# 04 - Acessibilidade WCAG 2.1

> Etapa "g" do projeto: aplicacao das diretrizes WCAG 2.1 (W3C, 2018).

Meta: conformidade **AA** em toda a aplicacao, com tema opcional **AAA** (alto contraste).

## Implementacoes por principio

### 1. Perceptivel

| Criterio | Nivel | Implementacao |
|----------|-------|---------------|
| 1.1.1 Conteudo nao textual | A | Icones em botoes tem `aria-label` ou tooltip; icones decorativos tem `aria-hidden="true"`. |
| 1.3.1 Info e relacionamentos | A | Toda label tem `<label for=...>`; secoes usam `<section aria-labelledby>`; listas usam `<ul role="list">`. |
| 1.3.4 Orientacao | AA | Layout responsivo - funciona em retrato e paisagem. |
| 1.3.5 Identificar finalidade do input | AA | Campos usam `autocomplete`/`autofillHints` (email, password). |
| 1.4.3 Contraste minimo | AA | Verde primario `#1B5E20` em branco = 9.5:1; texto cinza-700 em fundo claro = 7.4:1. |
| 1.4.4 Redimensionamento de texto | AA | CSS usa `rem`/`em` e variavel `--fonte-base`. Usuario pode trocar entre 14, 16, 18, 22 px. |
| 1.4.6 Contraste melhorado | AAA | Tema "alto contraste" disponivel: preto (#000) com amarelo (#FFFF00) = 19.6:1. |
| 1.4.10 Reflow | AA | Layout funciona em viewport de 320px sem scroll horizontal. |
| 1.4.11 Contraste nao textual | AA | Bordas de inputs e bordas de foco tem contraste >= 3:1. |
| 1.4.12 Espacamento de texto | AA | Sem `!important` em `letter-spacing`/`line-height` - usuario pode sobrescrever. |

### 2. Operavel

| Criterio | Nivel | Implementacao |
|----------|-------|---------------|
| 2.1.1 Teclado | A | Toda acao e acessivel por Tab/Enter/Espaco. Botoes sao `<button>`, nao `<div onclick>`. |
| 2.1.2 Sem armadilha de teclado | A | Modais fecham com Escape; menu mobile fecha com Escape. |
| 2.4.1 Pular blocos | A | Skip link "Pular para o conteudo principal" no topo de toda pagina. |
| 2.4.2 Pagina com titulo | A | Cada pagina tem `<title>` unico (slot `$title` no layout). |
| 2.4.3 Ordem do foco | A | Ordem do DOM = ordem visual. Sem `tabindex` positivo. |
| 2.4.4 Proposito do link | A | Links tem texto descritivo (sem "clique aqui"). |
| 2.4.6 Cabecalhos e labels | AA | Hierarquia h1-h2-h3 respeitada; labels descritivos. |
| 2.4.7 Foco visivel | AA | `*:focus-visible { outline: 3px solid; outline-offset: 2px; }` em todos elementos. |
| 2.5.5 Tamanho do alvo | AAA | Botoes tem `min-h-[44px]`. |

### 3. Compreensivel

| Criterio | Nivel | Implementacao |
|----------|-------|---------------|
| 3.1.1 Idioma da pagina | A | `<html lang="pt-BR">`. |
| 3.2.1 Em foco | A | Receber foco nao dispara mudanca de contexto. |
| 3.2.2 Em entrada | A | Mudar valor em input nao submete form automaticamente. |
| 3.2.4 Identificacao consistente | AA | Nav, botoes "Salvar/Cancelar" e icones repetidos sao consistentes em toda app. |
| 3.3.1 Identificacao de erro | A | `aria-invalid="true"` + `aria-describedby="campo-erro"` + texto vermelho. |
| 3.3.2 Labels ou instrucoes | A | Toda obrigatoriedade marcada com `*` + `aria-required="true"`. |
| 3.3.3 Sugestao de erro | AA | Mensagens de erro sao especificas ("E-mail invalido", nao "Erro"). |

### 4. Robusto

| Criterio | Nivel | Implementacao |
|----------|-------|---------------|
| 4.1.2 Nome, papel, valor | A | Componentes complexos (dropdown, chip, switch) usam ARIA roles corretos. |
| 4.1.3 Mensagens de status | AA | Confirmacao de presenca anuncia via `role="status" aria-live="polite"`. |

## Animacao reduzida (WCAG 2.3.3)

```css
html[data-reduzir-movimento="1"] *,
html[data-reduzir-movimento="1"] *::before,
html[data-reduzir-movimento="1"] *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
}

@media (prefers-reduced-motion: reduce) {
    /* mesmo bloco aplicado automaticamente */
}
```

No Flutter, isso e feito via `MediaQuery.of(context).copyWith(disableAnimations: true)`.

## Necessidades especificas declaradas

O usuario pode declarar (e o organizador pode adaptar a partida):
- Cadeirante / mobilidade reduzida
- Baixa visao / Cego
- Surdo / deficiencia auditiva
- Comunicacao em Libras
- Deficiencia cognitiva
- Transtorno do espectro autista

Locais de partida indicam `acessivel_cadeirante` para que o organizador escolha o adequado.

## Como testamos

- **axe-core (extensao Chrome):** rodar em `/dashboard`, `/patotas`, `/partidas/N`.
- **Leitor de tela:** NVDA (Windows) e VoiceOver (macOS/iOS).
- **Teclado:** navegar todo fluxo principal apenas com Tab/Shift-Tab/Enter.
- **Lighthouse:** meta de pontuacao Acessibilidade >= 95.
- **Checklist Sapo UX:** [https://ux.sapo.pt/checklists/](https://ux.sapo.pt/checklists/).

## Referencias
- W3C. Web Content Accessibility Guidelines (WCAG) 2.1, 2018.
- WCAG. Guia de consulta rapida. https://guia-wcag.com/.
- Silva Neto (2021). Acessibilidade em dispositivos moveis.
