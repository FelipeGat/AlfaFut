# 11 - Eventos e evidencias de campo

> Etapa "a" do projeto: contato inicial e registro fotografico.
> Estas fotos serao usadas como **evidencia da atividade de extensao** conforme requerido pelo PDF Uniasselvi (item: "Realizar registro fotografico").

## Visao geral

17 fotografias de partidas reais da comunidade que inspirou o AlfaFut. Servem a tres propositos:

1. **Evidencia de campo** para a comprovacao da atividade de extensao (etapa "a" do PDF, dia 1).
2. **Material de marketing** na pagina inicial do sistema (galeria publica).
3. **Validacao de personas** - personagens reais com idades, papeis e niveis distintos confirmam as personas descritas em `docs/01-personas.md`.

## Onde ficam

| Caminho | Uso |
|---------|-----|
| `docs/eventos/WhatsApp Image *.jpeg` | Originais como recebidos (evidencia para a Uniasselvi). |
| `public/images/eventos/evento-01.jpg` ate `evento-17.jpg` | Versoes renomeadas e servidas pelo Laravel para a galeria publica. |

## Galeria na aplicacao

A landing page (`/`) exibe 12 dessas fotos em grid de 4 colunas, usando `evento-01.jpg` como **hero image** com overlay verde do tema do AlfaFut.

A galeria respeita acessibilidade:
- `alt` descritivo em toda imagem (WCAG 1.1.1).
- `loading="lazy"` para performance.
- Lista semantica (`<ul role="list">`).

## Categorias observadas nas fotos

| Categoria | Quantidade aproximada |
|-----------|------------------------|
| Acao em jogo (chute, dribling, disputa) | ~9 |
| Retrato individual ou em dupla | ~5 |
| Comemoracao / abraco | ~3 |

Diversidade de uniformes confirmando o cenario de **patotas distintas competindo entre si** - exatamente o publico-alvo do AlfaFut.

## Como reusar

Para incluir uma foto especifica em outra parte do sistema:

```blade
<img src="{{ asset('images/eventos/evento-05.jpg') }}"
     alt="Descricao especifica do que a foto mostra"
     loading="lazy">
```

## Direitos de imagem

Antes de usar publicamente em material de divulgacao da Uniasselvi:
1. Confirmar com os jogadores fotografados.
2. Coletar termo de cessao de imagem (template em `docs/templates/`, a criar).
3. Em caso de menores de idade visiveis, obter autorizacao do responsavel legal.

> Nota para o estudante: as fotos foram entregues via WhatsApp em 29/04/2026 - antes de incluir na apresentacao final, certifique-se de ter os termos assinados.
