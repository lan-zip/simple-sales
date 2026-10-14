@props(['paginator'])
@if($paginator->hasPages())
    <nav class="ui-pagination" aria-label="Navigasi halaman">
        <p>Menampilkan {{ number_format($paginator->firstItem(), 0, ',', '.') }}–{{ number_format($paginator->lastItem(), 0, ',', '.') }} dari {{ number_format($paginator->total(), 0, ',', '.') }} data</p>
        <div class="ui-pagination-links">
            <x-ui.button :href="$paginator->previousPageUrl() ?? '#'" :disabled="$paginator->onFirstPage()" variant="secondary" rel="prev" aria-label="Halaman sebelumnya">Sebelumnya</x-ui.button>
            @foreach($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                <a href="{{ $url }}" class="ui-page-number" aria-label="Halaman {{ $page }}" @if($page === $paginator->currentPage()) aria-current="page" @endif>{{ $page }}</a>
            @endforeach
            <x-ui.button :href="$paginator->nextPageUrl() ?? '#'" :disabled="!$paginator->hasMorePages()" variant="secondary" rel="next" aria-label="Halaman berikutnya">Berikutnya</x-ui.button>
        </div>
    </nav>
@endif
