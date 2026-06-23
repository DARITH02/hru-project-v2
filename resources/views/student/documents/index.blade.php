@extends('layouts.app')

@section('content')
<div class="doc-page">
    <header class="doc-header">
        <div>
            <p class="doc-eyebrow">{{ __('admin.documents.student_eyebrow') }}</p>
            <h1 class="doc-title">{{ __('admin.documents.student_title') }}</h1>
            <p class="doc-subtitle">{{ __('admin.documents.student_subtitle') }}</p>
        </div>
        <div class="doc-stats">
            <div class="doc-stat is-approved"><strong>{{ $documents->count() }}</strong><span>{{ __('admin.documents.available') }}</span></div>
        </div>
    </header>

    <section class="doc-panel">
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>{{ __('admin.documents.document') }}</th>
                        <th>{{ __('admin.documents.description') }}</th>
                        <th>{{ __('admin.documents.class') }}</th>
                        <th>{{ __('admin.documents.teacher') }}</th>
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
                            <td>{{ $document->description ?: '-' }}</td>
                            <td>{{ $document->classRoom->name ?? '-' }}</td>
                            <td>{{ $document->teacher->user->name ?? '-' }}</td>
                            <td>{{ $document->created_at?->format('Y-m-d H:i') }}</td>
                            <td><a class="doc-link" href="{{ route('student.documents.download', $document) }}">{{ __('admin.documents.download') }}</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="doc-empty">{{ __('admin.documents.no_student_documents') }}</td>
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
