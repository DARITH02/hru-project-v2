<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentDocumentController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()?->student;
        abort_unless($student, 404, 'Student profile not found.');

        $documents = Document::with(['teacher.user', 'classRoom.subject', 'classRoom.groups'])
            ->where('status', Document::STATUS_APPROVED)
            ->latest()
            ->get()
            ->filter(fn (Document $document) => $document->isVisibleToStudent($student))
            ->values();

        $subjects = $documents
            ->pluck('classRoom.subject.name')
            ->filter()
            ->unique()
            ->values();

        return response()->json([
            'success' => true,
            'counts' => [
                'all' => $documents->count(),
                'subjects' => $subjects->count(),
            ],
            'subjects' => $subjects,
            'documents' => $documents->map(fn (Document $document) => $this->formatDocument($document))->values(),
        ]);
    }

    public function preview(Request $request, Document $document)
    {
        $this->authorizeStudentDocument($request, $document);
        abort_unless($this->canPreview($document->file_type), 422, 'Preview is not available for this file type.');

        return response()->file(Storage::path($document->file_path), [
            'Content-Disposition' => 'inline; filename="' . addslashes($document->original_name) . '"',
        ]);
    }

    public function download(Request $request, Document $document)
    {
        $this->authorizeStudentDocument($request, $document);

        return Storage::download($document->file_path, $document->original_name);
    }

    private function authorizeStudentDocument(Request $request, Document $document): void
    {
        $student = $request->user()?->student;

        abort_unless($student && $document->isApproved() && $document->isVisibleToStudent($student), 403);
        abort_unless(Storage::exists($document->file_path), 404);
    }

    private function formatDocument(Document $document): array
    {
        $class = $document->classRoom;
        $subject = $class?->subject?->name ?? $class?->name ?? 'Class document';
        $teacher = $document->teacher?->user?->name ?? 'Teacher';

        return [
            'id' => $document->id,
            'title' => $document->title,
            'description' => $document->description,
            'subject' => $subject,
            'class_id' => $document->class_id,
            'class_name' => $class?->name,
            'teacher' => $teacher,
            'type' => $this->fileTypeGroup($document->file_type),
            'ext' => $document->file_type,
            'size' => $this->formatSize($document->file_size),
            'file_size' => $document->file_size,
            'date' => optional($document->created_at)->toIso8601String(),
            'original_name' => $document->original_name,
            'can_preview' => $this->canPreview($document->file_type),
        ];
    }

    private function canPreview(?string $extension): bool
    {
        return in_array(strtolower((string) $extension), ['pdf', 'png', 'jpg', 'jpeg', 'webp'], true);
    }

    private function fileTypeGroup(?string $extension): string
    {
        return match (strtolower((string) $extension)) {
            'pdf' => 'pdf',
            'doc', 'docx' => 'doc',
            'ppt', 'pptx' => 'ppt',
            'png', 'jpg', 'jpeg', 'webp' => 'image',
            default => 'other',
        };
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes < 1024 * 1024) {
            return max(1, (int) round($bytes / 1024)) . ' KB';
        }

        return number_format($bytes / (1024 * 1024), 1) . ' MB';
    }
}
