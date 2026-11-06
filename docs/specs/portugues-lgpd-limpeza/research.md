# Research: Português, LGPD e limpeza

## Decision 1: Resposta única na recuperação

**Decision**: um serviço pede o link, ignora o resultado e devolve sempre a mesma mensagem, no site e na API. O envio roda depois da resposta, para o tempo de resposta não revelar a conta. A tela de nova senha mostra a mesma mensagem para token errado e e-mail sem conta.
**Rationale**: `Password::sendResetLink` devolve estados diferentes para conta inexistente e para bloqueio, e os dois controllers os mostravam.
**Alternatives considered**: só trocar o texto, com o envio dentro do pedido, que deixa a diferença de tempo.

## Decision 2: Validade de 60 dias para o acesso pela API

**Decision**: `sanctum.expiration` em 86.400 minutos, configurável por `SANCTUM_EXPIRATION`. A limpeza dos vencidos roda ao subir a imagem.
**Rationale**: o aplicativo não tem renovação de acesso; 60 dias evita pedir login a todo momento e limita o estrago de um token vazado. A imagem não tem agendador. Premissa a confirmar.
**Alternatives considered**: renovação automática, que exige mudar o aplicativo; agendador, que exige outro processo na imagem.

## Decision 3: Encerrar acessos na troca de senha

**Decision**: um serviço apaga os tokens da pessoa e as linhas dela na tabela de sessões, com exceção da sessão atual na troca pelo perfil, e troca o `remember_token`.
**Rationale**: a sessão fica no banco em produção (`SESSION_DRIVER=database`), então apagar a linha derruba o outro navegador na hora.
**Alternatives considered**: middleware `AuthenticateSession`, que só cobre sessões novas e não remove tokens.

## Decision 4: Dados do responsável em configuração

**Decision**: `config/alfafut.php` lê `ALFAFUT_RESPONSAVEL_NOME`, `ALFAFUT_RESPONSAVEL_DOCUMENTO` e `ALFAFUT_CONTATO_PRIVACIDADE`. `alfafut:checar-producao` recusa subir sem nome e contato.
**Rationale**: razão social, CNPJ e contato não estão no repositório, e a constitution I proíbe inventar. Fora de produção, a página mostra "a definir".
**Alternatives considered**: escrever no texto, que obrigaria a inventar.

## Decision 5: Aceite gravado no cadastro

**Decision**: duas colunas em `users`, fora do `$fillable`, preenchidas pelo cadastro do site e da API. A versão vem de `config('alfafut.versao_termos')`.
**Rationale**: é o que permite provar o que foi aceito sem criar tabela nova, já que novo aceite está fora desta frente.
**Alternatives considered**: tabela de aceites, útil só quando houver reaceite.

## Decision 6: Arquivo dos próprios dados em JSON

**Decision**: download JSON em `GET /perfil/meus-dados`, no site e na API, montado por um serviço só, com lista explícita de campos.
**Rationale**: lista explícita garante que senha e token não saiam por acidente; JSON é formato estruturado e sem dependência nova.
**Alternatives considered**: PDF, que não é reaproveitável; serializar os modelos, que vazaria campos novos.

## Decision 7: API sempre em JSON

**Decision**: em `bootstrap/app.php`, tudo em `api/*` renderiza JSON, com mensagens em português para 401, 404, 405 e 500, e sem redirecionar quem não entrou.
**Rationale**: o texto padrão de 404 expõe o nome da classe do modelo.
**Alternatives considered**: middleware que força o cabeçalho `Accept`, que não cobre rota inexistente.

## Decision 8: Envelope da resposta de criação fica como está

**Decision**: as respostas de criação continuam sem `data`, e a documentação diz isso rota a rota.
**Rationale**: o aplicativo Flutter não foi auditado, e trocar o formato pode quebrá-lo sem aviso.
**Alternatives considered**: padronizar agora, na próxima versão da API ou quando o aplicativo for auditado.

## Decision 9: Correção de texto só no texto

**Decision**: varredura por lista de palavras, limitada a texto entre tags, atributos de texto e literais de mensagem, com revisão do diff. Chaves, rotas, variáveis e valores gravados no banco ficam intactos.
**Rationale**: FR-017. `acao`, `situacao` e `proximas` também existem como identificadores.
**Alternatives considered**: substituição global, que quebraria rotas e campos.

## Decision 10: Banner de instalação por evento

**Decision**: o script dispara um evento na janela, e o banner abre ao recebê-lo; se o Alpine ainda não iniciou, o evento é repetido quando ele iniciar.
**Rationale**: `banner.__x` é da versão 2 do Alpine; o projeto usa a 3, então o banner nunca abria.
**Alternatives considered**: `Alpine.$data`, que depende da ordem de carregamento.
