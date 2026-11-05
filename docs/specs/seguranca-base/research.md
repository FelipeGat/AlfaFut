# Research: Base segura para o teste com pessoas reais

Sem `NEEDS CLARIFICATION` no Technical Context. Registra as decisões técnicas.

## Decision 1: Ponto único de permissão

**Decision**: Policies do Laravel, descobertas por convenção (`App\Policies\{Model}Policy`), com `before()` para o admin global.
**Rationale**: `authorizeMembro` está copiado em 8 controllers com regras diferentes: a API libera o admin global, o site não (`Api/PatotaController.php:156-162`, `Web/PatotaController.php:117-123`); e a API de despesas não tem nenhuma checagem. Policy é o mecanismo nativo, e web e API passam pelo mesmo objeto.
**Alternatives considered**: um trait com os métodos copiados (mantém a checagem opcional e sem padrão) e um middleware por rota (não enxerga o registro carregado com a mesma clareza).

## Decision 2: Visibilidade de dados de pessoa

**Decision**: `UsuarioResource` público por padrão; `MeuUsuarioResource` só nos endpoints da própria conta; `MembroResource` só na lista de membros.
**Rationale**: `UsuarioResource` aparece aninhado em Confirmação, Partida, Patota e Mensagem. Com o público como padrão, um lugar esquecido não vaza nada.
**Alternatives considered**: condicionais `when()` dentro de um único resource exigiriam saber a turma de contexto em cada ponto aninhado, e o erro seria vazar.

## Decision 3: Limite de tentativas

**Decision**: limitadores nomeados com `throttle:`, por e-mail + IP no login, IP em cadastro e recuperação, usuário no código de convite. Resposta 429 em português, com `Retry-After`.
**Rationale**: o login do site já limita por e-mail + IP (Breeze), e a API não limita nada. O nome único cobre os dois.
**Alternatives considered**: contar só as falhas no login da API, como a LoginRequest do site. Daria mais código, sem ganho no teste: cinco logins certos em um minuto não são uso normal.

## Decision 4: Confiança no proxy

**Decision**: lista por `TRUSTED_PROXIES`, vazia por padrão.
**Rationale**: com `*`, qualquer visitante forja `X-Forwarded-For` e escapa do limite por IP. Vazio é o seguro quando o servidor ainda não está definido.
**Alternatives considered**: `at: '*'`, descartado pelo motivo acima.

## Decision 5: Imagem de produção

**Decision**: nginx e PHP-FPM no mesmo contêiner, sob supervisor; MySQL externo; `php:8.3-fpm-alpine`; `storage` em volume.
**Rationale**: `artisan serve` não é servidor de produção, e o SQLite na imagem se perde a cada versão. Um contêiner só é o caminho mais simples de operar em qualquer servidor, e a escolha pode mudar na Etapa 9 sem refazer a aplicação.
**Alternatives considered**: FrankenPHP/Octane (exige revisar estado entre requisições) e dois contêineres nginx + FPM (mais peças, sem ganho em uma instância).

## Decision 6: Rota de login de demonstração

**Decision**: remover `/_demo-login`.
**Rationale**: a guarda atual é `APP_ENV=local`, e `.env.example` traz esse valor, então uma implantação mal configurada abriria o admin. Sem a rota, não há risco; o seed mostra a senha gerada e a varredura local entra pelo formulário.
**Alternatives considered**: manter a guarda de ambiente, o mínimo de FR-016, deixando o risco de configuração.

## Decision 7: Texto dos e-mails

**Decision**: `lang/pt_BR.json` com as frases dos e-mails e `User::preferredLocale()` devolvendo `pt_BR`. As notificações saem em português, e o resto da interface segue como está até a Etapa 5.
**Rationale**: a confirmação vira parte obrigatória do cadastro, e a constitution exige português em e-mails. Os modelos do framework já usam tradução, inclusive o rodapé, que `toMailUsing` não alcançaria. O arquivo também serve de início para a Etapa 5.
**Alternatives considered**: `toMailUsing` (deixaria o rodapé em inglês) e publicar as views de e-mail (mais arquivos para manter).
