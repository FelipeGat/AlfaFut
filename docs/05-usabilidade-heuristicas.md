# 05 - Usabilidade e Heuristicas de Nielsen

> Etapa "f" do projeto: aplicacao das 10 Heuristicas de Nielsen + Material Design 3.

## 10 Heuristicas de Nielsen aplicadas no AlfaFut

### H1 - Visibilidade do estado do sistema
- Spinner em botoes durante envio (`flutter` e `Alpine.js`).
- Snackbars confirmam acoes ("Presenca confirmada!", "Pagamento registrado").
- Indicadores "8/10 vagas", "posicao 2 da lista de espera".
- Skeleton loaders em listas (planejado).

### H2 - Correspondencia com mundo real
- Vocabulario do futebol: "patota", "pelada", "time", "vaga", "ratear".
- Datas em formato natural BR: "Sabado, 02 de maio as 15:00".
- Valores em real: "R$ 18,00".

### H3 - Controle e liberdade do usuario
- Botao "Cancelar" visivel em todos os formularios.
- Confirmacao para acoes destrutivas (`onsubmit="return confirm(...)"`).
- "Voltar" presente nas appbars do Flutter.
- Soft delete em patotas e despesas (reversivel).

### H4 - Consistencia e padroes
- Material Design 3 em web (Tailwind + tokens) e Flutter (Material 3 nativo).
- Cores semanticas: verde primario, laranja secundario, vermelho para erro.
- Botoes "stadium" (totalmente arredondados), cards com radius 16px.
- Mesma terminologia em todas as paginas.

### H5 - Prevencao de erro
- `validator` em todos campos do Flutter; `validate` em FormRequests do Laravel.
- Botao "Confirmar" em acoes destrutivas.
- `<input min/max>` em quantidade de jogadores e times.
- Feedback imediato em fonte/contraste (visualiza antes de salvar).

### H6 - Reconhecimento em vez de memorizacao
- Codigo de convite mostrado em destaque para o admin (nao precisa decorar).
- Lista de proximas partidas no dashboard (nao precisa procurar).
- Avatar com inicial do nome do membro.
- Status visual: chip "Confirmado" verde, "Espera" amarelo, "Recusado" vermelho.

### H7 - Flexibilidade e eficiencia
- Atalho do dashboard direto para "Confirmar presenca".
- "Entrar com codigo" disponivel em duas vias (botao no dashboard + dialog).
- Filtro proximas/passadas em /partidas.

### H8 - Estetica e design minimalista
- Hierarquia tipografica clara (h1-h2-h3 com tamanhos crescentes).
- Cards com whitespace generoso (`p-6`).
- Apenas a informacao necessaria por tela.

### H9 - Reconhecer, diagnosticar e recuperar erros
- Mensagens especificas: "E-mail invalido", "Use 8+ caracteres", "Codigo invalido".
- Erros em vermelho com `aria-live="polite"` para leitores de tela.
- Dica de recuperacao: "Tente outro codigo ou peca novo convite ao administrador".

### H10 - Ajuda e documentacao
- Texto explicativo em campos importantes (e.g. "Aparece em buscas - novos membros podem pedir para entrar").
- Tooltip em botoes com so icone.
- README e docs/ no repositorio.

## Material Design 3

### Tokens de cor
```
--md-sys-color-primary: #1B5E20    (verde futebol)
--md-sys-color-secondary: #F57C00  (laranja vibrante)
--md-sys-color-error: #BA1A1A
```

### Componentes usados
- **AppBar** (web e mobile)
- **Filled buttons** (acao primaria)
- **Outlined buttons** (acao secundaria)
- **Cards** com elevation 0 e radius 16
- **Chips** para status
- **TextField** com filled style
- **NavigationBar** (mobile, planejado para evolucao)
- **FAB** estendido para "Nova patota"

### Tipografia
Roboto via Bunny Fonts (sem bloqueio CORS, GDPR-friendly).
Escalas Material 3: displayLarge, headlineLarge/Medium, titleLarge, bodyLarge/Medium, labelLarge.

### Motion
- `prefers-reduced-motion` respeitado.
- Transicoes em 200-300ms quando ativadas.
- `Hero` em avatares (planejado).

## Referencias
- NIELSEN, J. 10 Usability Heuristics for User Interface Design (NN/g, 2020).
- GOOGLE. Material Design 3. https://m3.material.io/.
- TIDWELL et al. Designing Interfaces, 3 ed., 2020.
- TEXEIRA, F. Introducao e Boas Praticas em UX Design, 2014.
