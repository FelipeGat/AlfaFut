<footer class="bg-gray-900 text-gray-100 {{ $margem ?? 'mt-12' }}" role="contentinfo">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 text-sm text-center">
        <p>AlfaFut</p>
        <nav aria-label="Informações legais" class="mt-2 flex flex-wrap justify-center gap-x-4 gap-y-1">
            <a href="{{ route('privacidade') }}" class="underline text-gray-100">Política de privacidade</a>
            <a href="{{ route('termos') }}" class="underline text-gray-100">Termos de uso</a>
        </nav>
    </div>
</footer>
