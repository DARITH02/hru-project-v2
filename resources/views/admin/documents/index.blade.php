@extends('layouts.app')

@section('content')
<div class="doc-page">
    <header class="doc-header">
        <div>
            <p class="doc-eyebrow">{{ __('admin.documents.admin_eyebrow') }}</p>
            <h1 class="doc-title">{{ __('admin.documents.admin_title') }}</h1>
            <p class="doc-subtitle">{{ __('admin.documents.admin_subtitle') }}</p>
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

    @if ($errors->any())
        <div class="doc-alert is-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="doc-toolbar">
        <form class="doc-search" method="GET" action="{{ route('admin.documents.index') }}">
            <input type="hidden" name="status" value="{{ request('status', 'pending') }}">
            <input class="doc-input" type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('admin.documents.search_placeholder_admin') }}">
            <button class="doc-btn is-muted" type="submit">{{ __('admin.documents.search') }}</button>
        </form>
        <div class="doc-tabs">
            @foreach (['pending' => __('admin.documents.pending'), 'approved' => __('admin.documents.approved'), 'rejected' => __('admin.documents.rejected')] as $status => $label)
                <a class="doc-tab {{ request('status', 'pending') === $status ? 'is-active' : '' }}" href="{{ route('admin.documents.index', array_merge(request()->except('page'), ['status' => $status])) }}">
                    {{ $label }} {{ $counts[$status] ?? 0 }}
                </a>
            @endforeach
            @if (request('status', 'pending') === 'rejected' && Auth::user()?->isSuperAdmin() && ($counts['rejected'] ?? 0) > 0)
                <form method="POST" action="{{ route('admin.documents.rejected.destroy') }}" onsubmit="return window.confirmSubmit(event, '{{ __('admin.documents.delete_all_rejected_confirm') }}', {confirmText:'{{ __('admin.documents.delete_all_rejected') }}'});">
                    @csrf
                    @method('DELETE')
                    <button class="doc-btn is-red" type="submit">{{ __('admin.documents.delete_all_rejected') }}</button>
                </form>
            @endif
        </div>
    </div>

    <section class="doc-panel">
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>{{ __('admin.documents.document') }}</th>
                        <th>{{ __('admin.documents.teacher') }}</th>
                        <th>{{ __('admin.documents.class') }}</th>
                        <th>{{ __('admin.documents.uploaded') }}</th>
                        <th>{{ __('admin.documents.status') }}</th>
                        <th>{{ __('admin.documents.review') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documentsByTeacher as $teacherName => $teacherDocuments)
                        <tr class="doc-group-row">
                            <td colspan="6">
                                <div class="doc-group-title">
                                    <strong>{{ $teacherName }}</strong>
                                    <span>{{ __('admin.documents.teacher_group_count', ['count' => $teacherDocuments->count()]) }}</span>
                                </div>
                            </td>
                        </tr>

                        @foreach ($teacherDocuments as $document)
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
                                <td>{{ $document->teacher->user->name ?? '-' }}</td>
                                <td>{{ $document->classRoom->name ?? '-' }}</td>
                                <td>{{ $document->created_at?->format('Y-m-d H:i') }}</td>
                                <td><span class="doc-status is-{{ $document->status }}">{{ __('admin.documents.' . $document->status) }}</span></td>
                                <td>
                                    <div class="doc-review">
                                        <div class="doc-actions">
                                            <a class="doc-link" href="{{ route('admin.documents.preview', $document) }}" target="_blank">{{ __('admin.documents.preview') }}</a>
                                            <a class="doc-link" href="{{ route('admin.documents.download', $document) }}">{{ __('admin.documents.download') }}</a>
                                        </div>

                                        @if ($document->status === 'pending')
                                            <form method="POST" action="{{ route('admin.documents.approve', $document) }}">
                                                @csrf
                                                <button class="doc-btn is-green" type="submit">{{ __('admin.documents.approve') }}</button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.documents.reject', $document) }}">
                                                @csrf
                                                <label class="doc-label" for="rejected_reason_{{ $document->id }}">{{ __('admin.documents.reject_reason') }}</label>
                                                <textarea class="doc-textarea" id="rejected_reason_{{ $document->id }}" name="rejected_reason" required></textarea>
                                                <button class="doc-btn is-red" type="submit">{{ __('admin.documents.reject') }}</button>
                                            </form>
                                        @elseif ($document->status === 'rejected')
                                            <div class="doc-file-meta">{{ $document->rejected_reason }}</div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" class="doc-empty">{{ __('admin.documents.no_documents_filter') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('styles')
    @include('documents._styles')
@endpush
