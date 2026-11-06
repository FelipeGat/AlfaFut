@php
    $aDefinir = 'a definir antes da publicação';
    $responsavel = config('alfafut.responsavel');
@endphp

<x-guest-layout largura="max-w-3xl">
    <x-slot name="title">Política de privacidade</x-slot>

    <article class="space-y-6 text-sm sm:text-base leading-relaxed">
        <header>
            <h1 class="text-2xl font-bold">Política de privacidade</h1>
            <p class="text-sm text-gray-700 mt-1">Versão de {{ \Illuminate\Support\Carbon::parse(config('alfafut.versao_termos'))->translatedFormat('d/m/Y') }}</p>
        </header>

        <p>Esta página explica quais dados o AlfaFut guarda, para quê, quem pode ver e como você controla os seus dados, conforme a Lei Geral de Proteção de Dados (Lei 13.709/2018).</p>

        <section aria-labelledby="responsavel-h" class="space-y-2">
            <h2 id="responsavel-h" class="text-lg font-semibold">Quem responde pelos dados</h2>
            <ul class="list-disc pl-5 space-y-1">
                <li>Responsável: {{ $responsavel['nome'] ?: $aDefinir }}</li>
                @if ($responsavel['documento'])
                    <li>Documento: {{ $responsavel['documento'] }}</li>
                @endif
                <li>Contato de privacidade: {{ $responsavel['contato_privacidade'] ?: $aDefinir }}</li>
            </ul>
        </section>

        <section aria-labelledby="dados-h" class="space-y-2">
            <h2 id="dados-h" class="text-lg font-semibold">Quais dados guardamos</h2>
            <ul class="list-disc pl-5 space-y-1">
                <li><strong>Cadastro:</strong> nome, apelido, e-mail, telefone, foto de perfil, data de nascimento, posição, nível, perfil de uso e a data em que você aceitou estes termos. A senha é guardada de forma cifrada, sem que ninguém consiga lê-la.</li>
                <li><strong>Acessibilidade:</strong> alto contraste, tamanho da fonte, redução de movimento, leitor de tela e as necessidades que você escolher declarar. Essas necessidades podem revelar dado de saúde e só são guardadas se você as informar.</li>
                <li><strong>Uso na turma:</strong> turmas e papel em cada uma, presença e lista de espera, times, gols, parcelas, pagamentos registrados, histórico de mudanças no dinheiro da turma e mensagens do mural.</li>
                <li><strong>Campos:</strong> se você cadastra um campo, guardamos os dados dele, inclusive o WhatsApp de contato.</li>
                <li><strong>Dados técnicos:</strong> endereço de rede e navegador da sessão, além do registro de acessos do servidor, usados para manter o sistema seguro.</li>
            </ul>
        </section>

        <section aria-labelledby="finalidade-h" class="space-y-2">
            <h2 id="finalidade-h" class="text-lg font-semibold">Para que usamos e com qual base legal</h2>
            <ul class="list-disc pl-5 space-y-1">
                <li>Para oferecer o sistema que você pediu, como organizar a turma, a partida, os times e o rateio, com base na execução do contrato (art. 7º, V).</li>
                <li>Para proteger as contas e evitar abuso, com base no legítimo interesse (art. 7º, IX).</li>
                <li>Para adaptar o sistema e avisar quem organiza a sua turma sobre as necessidades que você declarar, com base no seu consentimento (art. 11, I). Você pode retirá-lo a qualquer momento, apagando essas informações em Acessibilidade.</li>
                <li>Para enviar os e-mails de confirmação de conta e de redefinição de senha.</li>
            </ul>
        </section>

        <section aria-labelledby="quem-ve-h" class="space-y-2">
            <h2 id="quem-ve-h" class="text-lg font-semibold">Quem pode ver</h2>
            <ul class="list-disc pl-5 space-y-1">
                <li><strong>Membros da sua turma:</strong> nome, apelido, foto, posição, nível, presença, times, gols e as mensagens do mural.</li>
                <li><strong>Quem organiza a sua turma:</strong> também o seu e-mail, telefone e necessidades de acessibilidade, as suas parcelas e pagamentos.</li>
                <li><strong>Pessoas com conta no AlfaFut:</strong> os campos publicados no catálogo, com o WhatsApp de contato.</li>
                <li><strong>Equipe que administra o AlfaFut:</strong> dados de conta e de turma, quando necessário para suporte e segurança.</li>
            </ul>
        </section>

        <section aria-labelledby="compartilhamento-h" class="space-y-2">
            <h2 id="compartilhamento-h" class="text-lg font-semibold">Com quem compartilhamos</h2>
            <p>O AlfaFut não vende dados pessoais. Alguns serviços são usados para o sistema funcionar:</p>
            <ul class="list-disc pl-5 space-y-1">
                <li>a empresa de hospedagem, onde os dados ficam guardados;</li>
                <li>o serviço de envio de e-mail, que recebe o seu endereço para entregar as mensagens do sistema;</li>
                <li>o Bunny Fonts, que entrega as fontes das páginas e recebe o endereço de rede de quem as abre.</li>
            </ul>
            <p>O botão de WhatsApp de um campo abre o WhatsApp, e o uso dele segue as regras desse serviço.</p>
        </section>

        <section aria-labelledby="cookies-h" class="space-y-2">
            <h2 id="cookies-h" class="text-lg font-semibold">Cookies</h2>
            <p>Usamos só os cookies necessários para manter você conectado, proteger os formulários e, se você pedir, lembrar do seu login. Não usamos cookies de publicidade nem de análise de audiência. O aplicativo instalado guarda no aparelho apenas arquivos públicos, como imagens e estilos, e nenhuma tela com dados da turma.</p>
        </section>

        <section aria-labelledby="direitos-h" class="space-y-2">
            <h2 id="direitos-h" class="text-lg font-semibold">Seus direitos</h2>
            <ul class="list-disc pl-5 space-y-1">
                <li><strong>Ver e corrigir:</strong> em <a href="{{ route('profile.edit') }}" class="underline">Meu perfil</a> e em Acessibilidade, com login.</li>
                <li><strong>Baixar uma cópia:</strong> em Meu perfil, no botão de baixar os seus dados.</li>
                <li><strong>Excluir a conta:</strong> em Meu perfil. Veja abaixo o que acontece.</li>
                <li><strong>Outros pedidos:</strong> informações sobre o tratamento, revisão, revogação do consentimento e demais direitos do art. 18 pelo contato de privacidade acima.</li>
            </ul>
        </section>

        <section aria-labelledby="exclusao-h" class="space-y-2">
            <h2 id="exclusao-h" class="text-lg font-semibold">O que acontece ao excluir a conta</h2>
            <ul class="list-disc pl-5 space-y-1">
                <li>São apagados: nome, apelido, e-mail, telefone, foto, data de nascimento, posição, nível, preferências e necessidades de acessibilidade, senha e acessos.</li>
                <li>Você sai das turmas, e a sua vaga em partidas futuras é liberada.</li>
                <li>Partidas, placares, despesas e parcelas antigas continuam na turma sob o nome "Conta excluída", para os totais dos outros continuarem certos.</li>
                <li>As mensagens que você escreveu continuam no mural, sob "Conta excluída". Apague antes as que não quiser deixar, ou peça pelo contato de privacidade.</li>
                <li>Os campos que você cadastrou saem do catálogo, sem o WhatsApp.</li>
            </ul>
        </section>

        <section aria-labelledby="menores-h" class="space-y-2">
            <h2 id="menores-h" class="text-lg font-semibold">Menores de idade</h2>
            <p>O AlfaFut é para maiores de 18 anos. Menores só podem usar com autorização do responsável legal.</p>
        </section>

        <section aria-labelledby="mudancas-h" class="space-y-2">
            <h2 id="mudancas-h" class="text-lg font-semibold">Mudanças nesta política</h2>
            <p>Quando esta política mudar, a data da versão acima será atualizada.</p>
        </section>
    </article>
</x-guest-layout>
