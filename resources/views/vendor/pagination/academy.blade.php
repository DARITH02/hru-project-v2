@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="academy-pagination">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span aria-disabled="true" class="page-link disabled">&laquo;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="page-link">&laquo;</a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span aria-disabled="true" class="page-link dots">{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="page-link active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="page-link">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="page-link">&raquo;</a>
        @else
            <span aria-disabled="true" class="page-link disabled">&raquo;</span>
        @endif
    </nav>
@endif
