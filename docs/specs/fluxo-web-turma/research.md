# Research: Fluxo da turma pelo site

## Decision 1: fuso e idioma entram nesta frente

**Decision**: fuso `America/Sao_Paulo` e idioma `pt_BR` por padrão, com mensagens de validação em `lang/pt_BR/validation.php`.
**Rationale**: `config/app.php:68` está em UTC. Às 14:30 de Brasília, uma partida às 15:00 seria recusada como passada, e a das 20:00 sairia de "Próximas" às 17:00. Sem isso o formulário nasce errado. O item 33 estava previsto para a Etapa 4 e foi antecipado.
**Alternatives considered**: guardar UTC e converter na tela. Exigiria mexer em todas as telas e consultas, sem ganho para um produto só no Brasil.

## Decision 2: regras de vínculo em serviço com exceção própria

**Decision**: `MembrosDaTurma` concentra entrar, sair, remover, liberar, mudar papel e transferir. A recusa de regra é `RegraDaTurma`, que responde `{mensagem}` em 422 na API e volta com aviso no site.
**Rationale**: a constitution II pede ponto único, e a API já responde `{mensagem}`, formato que o app pode estar lendo.
**Alternatives considered**: repetir a regra nos dois controllers, a causa dos itens 2 e 5 da auditoria.

## Decision 3: validação de partida em um único Form Request

**Decision**: `SalvarPartidaRequest` autoriza e valida criar e alterar, no site e na API. O campo de tela `lugar` vira `local_id` ou `campo_id` antes de validar.
**Rationale**: autorizar no Form Request garante 403 antes de 422 para quem não pode, como a matriz exige. Um só seletor dispensa script e impede escolher dois lugares.
**Alternatives considered**: dois seletores com script para limpar o outro, frágil em teclado e leitor de tela.

## Decision 4: situação `removido` no vínculo

**Decision**: remover grava `status = removido`; a entrada pelo código recusa até liberarem, o que volta a situação para `inativo`.
**Rationale**: sem isso a remoção é desfeita com o mesmo código. Não exige migração.
**Alternatives considered**: trocar o código de convite, o que invalidaria o convite já enviado a todos.

## Decision 5: quem gerencia membros

**Decision**: criador e responsável gerenciam; organizador comum só vê os contatos. O administrador da plataforma passa pelo `before()` das Policies.
**Rationale**: evita um organizador remover outro. Marcada como premissa para o Felipe conferir.
**Alternatives considered**: todos os organizadores gerenciarem, com regra extra para organizador não mexer em organizador.

## Decision 6: endereço do local checado no controller

**Decision**: `abort_unless($local->patota_id === $patota->id, 404)`, em vez de `scoped()`.
**Rationale**: `scoped()` procuraria a relação `locals`, plural em inglês, que não existe em `Patota`.
**Alternatives considered**: sobrescrever `childRouteBindingRelationshipName`, mais código para o mesmo efeito.
