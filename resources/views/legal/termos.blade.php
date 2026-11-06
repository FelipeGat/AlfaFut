@php
    $aDefinir = 'a definir antes da publicação';
    $responsavel = config('alfafut.responsavel');
@endphp

<x-guest-layout largura="max-w-3xl">
    <x-slot name="title">Termos de uso</x-slot>

    <article class="space-y-6 text-sm sm:text-base leading-relaxed">
        <header>
            <h1 class="text-2xl font-bold">Termos de uso</h1>
            <p class="text-sm text-gray-700 mt-1">Versão de {{ \Illuminate\Support\Carbon::parse(config('alfafut.versao_termos'))->translatedFormat('d/m/Y') }}</p>
        </header>

        <section aria-labelledby="sistema-h" class="space-y-2">
            <h2 id="sistema-h" class="text-lg font-semibold">O que é o AlfaFut</h2>
            <p>O AlfaFut é um sistema para organizar a pelada: turma, partida, presença, lista de espera, sorteio de times, placar, mural, despesas e catálogo de campos. Ele é oferecido por {{ $responsavel['nome'] ?: $aDefinir }}.</p>
        </section>

        <section aria-labelledby="conta-h" class="space-y-2">
            <h2 id="conta-h" class="text-lg font-semibold">Sua conta</h2>
            <ul class="list-disc pl-5 space-y-1">
                <li>Você precisa ter 18 anos ou mais, ou a autorização do seu responsável legal.</li>
                <li>Use dados verdadeiros e confirme o seu e-mail.</li>
                <li>Você responde pelo que é feito com a sua senha. Se suspeitar de uso indevido, troque a senha: isso encerra os outros acessos à conta.</li>
            </ul>
        </section>

        <section aria-labelledby="dinheiro-h" class="space-y-2">
            <h2 id="dinheiro-h" class="text-lg font-semibold">O AlfaFut não movimenta dinheiro</h2>
            <p>O AlfaFut só registra despesas, rateios e pagamentos informados pelas pessoas da turma. Ele não cobra, não recebe e não transfere dinheiro. Acertos e eventuais diferenças são resolvidos entre as pessoas da turma.</p>
        </section>

        <section aria-labelledby="turma-h" class="space-y-2">
            <h2 id="turma-h" class="text-lg font-semibold">Turmas e campos</h2>
            <ul class="list-disc pl-5 space-y-1">
                <li>Quem organiza a turma define partidas, vagas, membros e despesas, e responde por essas decisões.</li>
                <li>O catálogo mostra os dados informados por quem cadastrou cada campo. Reserva, preço e condições são combinados direto com o dono do campo.</li>
            </ul>
        </section>

        <section aria-labelledby="conduta-h" class="space-y-2">
            <h2 id="conduta-h" class="text-lg font-semibold">Uso correto</h2>
            <ul class="list-disc pl-5 space-y-1">
                <li>Não publique conteúdo ofensivo, ilegal ou dados de outras pessoas sem autorização.</li>
                <li>Não tente acessar dados de outras turmas nem atrapalhar o funcionamento do sistema.</li>
                <li>Contas que descumprirem estes termos podem ser suspensas.</li>
            </ul>
        </section>

        <section aria-labelledby="disponibilidade-h" class="space-y-2">
            <h2 id="disponibilidade-h" class="text-lg font-semibold">Fase de teste</h2>
            <p>O AlfaFut está em fase de teste e o uso é gratuito durante esse período. Funções podem mudar e o sistema pode ficar indisponível para manutenção. Antes de qualquer cobrança, avisaremos com antecedência.</p>
        </section>

        <section aria-labelledby="dados-h" class="space-y-2">
            <h2 id="dados-h" class="text-lg font-semibold">Seus dados e o fim da conta</h2>
            <p>O tratamento dos seus dados está na <a href="{{ route('privacidade') }}" class="underline">política de privacidade</a>. Você pode excluir a sua conta a qualquer momento em Meu perfil.</p>
        </section>

        <section aria-labelledby="contato-h" class="space-y-2">
            <h2 id="contato-h" class="text-lg font-semibold">Mudanças e contato</h2>
            <p>Quando estes termos mudarem, a data da versão acima será atualizada. Eles seguem a lei brasileira. Fale com a gente em {{ $responsavel['contato_privacidade'] ?: $aDefinir }}.</p>
        </section>
    </article>
</x-guest-layout>
