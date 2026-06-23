@extends('layouts.app')

@section('content')
<div class="doc-page">
    <header class="doc-header">
        <div>
            <p class="doc-eyebrow">{{ __('admin.documents.teacher_eyebrow') }}</p>
            <h1 class="doc-title">{{ __('admin.documents.teacher_title') }}</h1>
            <p class="doc-subtitle">{{ __('admin.documents.teacher_subtitle') }}</p>
        </div>
        <div class="doc-stats">
            <div class="doc-stat is-pending"><strong>{{ $counts['pending'] ?? 0 }}</strong><span>{{ __('admin.documents.pending') }}</span></div>
            <div class="doc-stat is-approved"><strong>{{ $counts['approved'] ?? 0 }}</strong><span>{{ __('admin.documents.approved') }}</span></div>
            <div class="doc-stat is-rejected"><strong>{{ $counts['rejected'] ?? 0 }}</strong><span>{{ __('admin.documents.rejected') }}</span></div>
        </div>
    </header>

    @if (session('success'))
        <div class="doc-alert is-success">{{ session('success') }}</div>
    @endif

    <form class="doc-toolbar" method="GET" action="{{ route('teacher.documents.index') }}">
        <div class="doc-search">
            <input class="doc-input" type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('admin.documents.search_placeholder_teacher') }}">
            <button class="doc-btn is-muted" type="submit">{{ __('admin.documents.search') }}</button>
        </div>
        <div class="doc-tabs">
            <a class="doc-tab {{ request('status') === null ? 'is-active' : '' }}" href="{{ route('teacher.documents.index', request()->except('status', 'page')) }}">{{ __('admin.documents.all') }} {{ $counts['all'] ?? 0 }}</a>
            @foreach (['pending' => __('admin.documents.pending'), 'approved' => __('admin.documents.approved'), 'rejected' => __('admin.documents.rejected')] as $status => $label)
                <a class="doc-tab {{ request('status') === $status ? 'is-active' : '' }}" href="{{ route('teacher.documents.index', array_merge(request()->except('page'), ['status' => $status])) }}">
                    {{ $label }} {{ $counts[$status] ?? 0 }}
                </a>
            @endforeach
            <a class="doc-btn is-primary" href="{{ route('teacher.documents.create') }}">{{ __('admin.documents.upload') }}</a>
        </div>
    </form>

    <section class="doc-panel">
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>{{ __('admin.documents.document') }}</th>
                        <th>{{ __('admin.documents.class') }}</th>
                        <th>{{ __('admin.documents.status') }}</th>
                        <th>{{ __('admin.documents.reject_reason') }}</th>
                        <th>{{ __('admin.documents.uploaded') }}</th>
                        <th>{{ __('admin.documents.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $document)
                        @php
                            $type = strtolower($document->file_type);
                            $size = $document->file_size ? number_format($document->file_size / 1048576, 1) . ' MB' : '-';
                        @endphp
                        <tr>
                            <td>
                                <div class="doc-file">
                                    <span class="doc-file-badge is-{{ $type }}">{{ $type }}</span>
                                    <span>
                                        <span class="doc-file-title">{{ $document->title }}</span>
                                        <span class="doc-file-meta">{{ $document->original_name }} · {{ $size }}</span>
                                    </span>
                                </div>
                            </td>
                            <td>{{ $document->classRoom->name ?? '-' }}</td>
                            <td><span class="doc-status is-{{ $document->status }}">{{ __('admin.documents.' . $document->status) }}</span></td>
                            <td>{{ $document->status === 'rejected' ? $document->rejected_reason : '-' }}</td>
                            <td>{{ $document->created_at?->format('Y-m-d H:i') }}</td>
                            <td><a class="doc-link" href="{{ route('teacher.documents.download', $document) }}">{{ __('admin.documents.download') }}</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="doc-empty">{{ __('admin.documents.no_documents') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{ $documents->links() }}
</div>
@endsection

@push('styles')
    @include('documents._styles')
@endpush
