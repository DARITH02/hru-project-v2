@extends('layouts.app')

@section('content')
<div class="doc-page">
    <header class="doc-header">
        <div>
            <p class="doc-eyebrow">{{ __('admin.documents.upload_eyebrow') }}</p>
            <h1 class="doc-title">{{ __('admin.documents.upload_title') }}</h1>
            <p class="doc-subtitle">{{ __('admin.documents.upload_subtitle') }}</p>
        </div>
        <div class="doc-tabs">
            <a class="doc-link" href="{{ route('teacher.documents.index') }}">{{ __('admin.documents.my_documents') }}</a>
        </div>
    </header>

    @if ($errors->any())
        <div class="doc-alert is-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="doc-form" method="POST" action="{{ route('teacher.documents.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="doc-field">
            <label class="doc-label" for="class_id">{{ __('admin.documents.class') }}</label>
            <select class="doc-select" id="class_id" name="class_id" required>
                <option value="">{{ __('admin.documents.select_class') }}</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}" @selected(old('class_id') == $class->id)>
                        {{ $class->name }} @if($class->subject) - {{ $class->subject->name }} @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div class="doc-field">
            <label class="doc-label" for="title">{{ __('admin.documents.document_title') }}</label>
            <input class="doc-input" id="title" name="title" type="text" value="{{ old('title') }}" required>
        </div>

        <div class="doc-field">
            <label class="doc-label" for="description">{{ __('admin.documents.description') }}</label>
            <textarea class="doc-textarea" id="description" name="description">{{ old('description') }}</textarea>
        </div>

        <div class="doc-field">
            <label class="doc-label" for="file">{{ __('admin.documents.file') }}</label>
            <input class="doc-input" id="file" name="file" type="file" accept=".pdf,.doc,.docx,.ppt,.pptx" required>
            <span class="doc-file-meta">{{ __('admin.documents.allowed_files') }}</span>
        </div>

        <div class="doc-actions">
            <button class="doc-btn is-primary" type="submit">{{ __('admin.documents.upload_document') }}</button>
            <a class="doc-link" href="{{ route('teacher.documents.index') }}">{{ __('admin.documents.cancel') }}</a>
        </div>
    </form>
</div>
@endsection

@push('styles')
    @include('documents._styles')
@endpush
