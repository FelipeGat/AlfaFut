# 02 - Requisitos

> Etapas "j" e "m" do projeto: identificacao e especificacao de requisitos (PDF Uniasselvi).

## Requisitos funcionais (RF)

| ID | Modulo | Descricao | Prioridade |
|----|--------|-----------|------------|
| RF01 | Cadastro | O usuario deve poder se cadastrar com nome, e-mail, senha, apelido, telefone, posicao preferida e nivel de habilidade. | Alta |
| RF02 | Login | O sistema deve autenticar via e-mail e senha (Sanctum no app). | Alta |
| RF03 | Patota | O usuario deve poder criar uma patota informando nome, cidade, jogadores por time, quantidade de times e mensalidade. | Alta |
| RF04 | Patota | O criador da patota deve poder convidar membros via codigo unico de 8 caracteres. | Alta |
| RF05 | Patota | Os membros devem poder entrar em uma patota informando o codigo de convite. | Alta |
| RF06 | Partida | O administrador deve poder criar uma partida informando local, data, hora, duracao, vagas e valor por jogador. | Alta |
| RF07 | Partida | Os membros devem poder confirmar presenca em uma partida em 1 toque. | Alta |
| RF08 | Partida | Quando a partida estiver cheia, novas confirmacoes devem ser inseridas em lista de espera ordenada. | Alta |
| RF09 | Partida | Quando alguem cancelar, o sistema deve promover automaticamente o primeiro da lista de espera. | Alta |
| RF10 | Times | O administrador deve poder formar times balanceando posicao e nivel de habilidade. | Media |
| RF11 | Despesas | O administrador deve poder lancar despesas (locacao, arbitragem, material, alimentacao). | Alta |
| RF12 | Despesas | Quando uma despesa for marcada como "rateada", o sistema deve dividir igualmente entre os confirmados na partida. | Alta |
| RF13 | Pagamentos | O membro deve poder registrar seu pagamento informando forma (PIX, dinheiro, cartao, transferencia). | Alta |
| RF14 | Mensagens | Os membros devem poder enviar mensagens para o mural da patota e para uma partida especifica. | Media |
| RF15 | Mensagens | O administrador deve poder fixar mensagens importantes no topo do mural. | Baixa |
| RF16 | Acessibilidade | O usuario deve poder configurar alto contraste, tamanho de fonte, reducao de movimento e otimizacao para leitor de tela. | Alta |
| RF17 | Acessibilidade | O usuario deve poder declarar suas necessidades especificas (cadeirante, surdo, baixa visao etc.) para que o organizador adapte. | Alta |
| RF18 | Local | O cadastro de local deve indicar se possui vestiario, estacionamento e acessibilidade para cadeirantes. | Alta |

## Requisitos nao funcionais (RNF)

### Acessibilidade (WCAG 2.1)
| ID | Descricao | Criterio |
|----|-----------|----------|
| RNF01 | Toda funcionalidade deve ser operavel por teclado. | WCAG 2.1.1 - Keyboard |
| RNF02 | Foco visivel deve ser destacado em todo elemento interativo. | WCAG 2.4.7 - Focus Visible |
| RNF03 | Contraste minimo 4.5:1 para texto e 3:1 para componentes UI. | WCAG 1.4.3 - Contrast (Minimum) |
| RNF04 | Suporte a tema de alto contraste com razao 7:1+. | WCAG 1.4.6 - Contrast (Enhanced) |
| RNF05 | Texto deve ser redimensionavel ate 200% sem perda de funcionalidade. | WCAG 1.4.4 - Resize Text |
| RNF06 | Animacoes devem respeitar `prefers-reduced-motion` e preferencia do usuario. | WCAG 2.3.3 - Animation from Interactions |
| RNF07 | Formularios devem ter labels visiveis associados aos campos. | WCAG 1.3.1 - Info and Relationships |
| RNF08 | Erros devem ser anunciados via `aria-live` para leitores de tela. | WCAG 4.1.3 - Status Messages |
| RNF09 | Imagens funcionais devem ter texto alternativo; decorativas devem ter `aria-hidden`. | WCAG 1.1.1 - Non-text Content |
| RNF10 | Skip link "Pular para conteudo" deve estar disponivel. | WCAG 2.4.1 - Bypass Blocks |

### Usabilidade e UX
| ID | Descricao | Heuristica de Nielsen |
|----|-----------|------------------------|
| RNF11 | O sistema deve manter o usuario informado do estado (loading, sucesso, erro). | H1 - Visibility of system status |
| RNF12 | Linguagem da interface deve ser em portugues coloquial brasileiro. | H2 - Match between system and real world |
| RNF13 | Toda acao destrutiva deve poder ser revertida ou exigir confirmacao. | H3 - User control |
| RNF14 | Padroes Material Design 3 devem ser aplicados consistentemente. | H4 - Consistency |
| RNF15 | Validacao client-side deve prevenir erros antes do envio. | H5 - Error prevention |
| RNF16 | Atalhos para confirmacao de partida devem estar disponiveis no dashboard. | H7 - Flexibility and efficiency |

### Tecnicos
| ID | Descricao |
|----|-----------|
| RNF17 | Tempo de resposta da API < 500ms para 95% das requisicoes em base com 10k registros. |
| RNF18 | App mobile deve funcionar offline para visualizacao das proximas partidas (cache local). |
| RNF19 | Senhas devem ser armazenadas com hash bcrypt. |
| RNF20 | Tokens de API devem ser gerados via Laravel Sanctum (revogaveis por dispositivo). |
| RNF21 | Sistema deve registrar log de autenticacao e mudancas em despesas. |
| RNF22 | Codigo deve seguir PSR-12 (PHP) e dart_lints (Flutter). |

## Restricoes
- O backend roda em PHP 8.2+ (XAMPP).
- O app mobile alvo principal e Android 8+ e iOS 13+.
- O banco padrao em desenvolvimento e SQLite; produc=ao usara MySQL/MariaDB.

## Casos de uso principais

```
UC01 - Criar patota
  Ator: Administrador
  Pre: Estar autenticado.
  Fluxo: Acessa "Nova patota" -> preenche nome, jogadores por time, quantidade de times, mensalidade -> sistema gera codigo de convite -> compartilha codigo.

UC02 - Confirmar presenca
  Ator: Membro
  Pre: Ser membro da patota.
  Fluxo: Recebe partida no dashboard -> toca em "Confirmar presenca" -> se ha vaga, e adicionado aos confirmados; se nao, vai para lista de espera.

UC03 - Lancar e ratear despesa
  Ator: Administrador
  Pre: Existir uma partida.
  Fluxo: Acessa partida -> "Nova despesa" -> informa valor e marca "ratear" -> sistema cria pagamento individual para cada confirmado, dividindo o valor.

UC04 - Configurar acessibilidade
  Ator: Qualquer usuario
  Fluxo: Acessa "Acessibilidade" -> seleciona alto contraste, fonte e necessidades -> aplicacao reaplica imediatamente.
```
