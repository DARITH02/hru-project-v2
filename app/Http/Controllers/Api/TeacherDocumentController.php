<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TeacherDocumentController extends Controller
{
    private const MAX_FILES_PER_BATCH = 100;
    private const MAX_FILE_KB = 102400;
    private const MAX_BATCH_BYTES = 1024 * 1024 * 1024;

    public function index(Request $request)
    {
        $teacher = $this->currentTeacher($request);

        $documents = Document::with(['classRoom.subject'])
            ->where('teacher_id', $teacher->id)
            ->latest()
            ->limit(200)
            ->get();

        $counts = [
            'all' => $documents->count(),
            Document::STATUS_PENDING => $documents->where('status', Document::STATUS_PENDING)->count(),
            Document::STATUS_APPROVED => $documents->where('status', Document::STATUS_APPROVED)->count(),
            Document::STATUS_REJECTED => $documents->where('status', Document::STATUS_REJECTED)->count(),
        ];

        return response()->json([
            'success' => true,
            'counts' => $counts,
            'documents' => $documents->map(fn (Document $document) => $this->formatDocument($document))->values(),
        ]);
    }

    public function store(Request $request)
    {
        $teacher = $this->currentTeacher($request);

        $data = $request->validate([
            'documents' => ['required', 'array', 'min:1', 'max:' . self::MAX_FILES_PER_BATCH],
            'documents.*.class_id' => [
                'required',
                Rule::exists('classes', 'id')->where(fn ($query) => $query->where('teacher_id', $teacher->id)),
            ],
            'documents.*.title' => ['required', 'string', 'max:255'],
            'documents.*.description' => ['nullable', 'string', 'max:5000'],
            'documents.*.file' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,ppt,pptx,png,jpg,jpeg,webp',
                'max:' . self::MAX_FILE_KB,
            ],
        ]);

        $files = collect($request->file('documents', []))->pluck('file')->filter();
        $batchBytes = $files->sum(fn ($file) => $file->getSize());

        if ($batchBytes > self::MAX_BATCH_BYTES) {
            throw ValidationException::withMessages([
                'documents' => 'Upload batch is too large. Please keep one batch under 1 GB.',
            ]);
        }

        $storedPaths = [];

        try {
            $documents = DB::transaction(function () use ($data, $request, $teacher, &$storedPaths) {
                $created = collect();

                foreach ($data['documents'] as $index => $item) {
                    $file = $request->file("documents.$index.file");
                    $path = $file->store('teacher-documents');
                    $storedPaths[] = $path;

                    $created->push(Document::create([
                        'class_id' => $item['class_id'],
                        'teacher_id' => $teacher->id,
                        'title' => $item['title'],
                        'description' => $item['description'] ?? null,
                        'original_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => strtolower($file->getClientOriginalExtension()),
                        'file_size' => $file->getSize(),
                        'status' => Document::STATUS_PENDING,
                    ]));
                }

                return $created;
            });
        } catch (\Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::delete($path);
            }

            throw $exception;
        }

        $documents = Document::with(['classRoom.subject'])
            ->whereKey($documents->pluck('id'))
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => $documents->count() . ' document(s) uploaded and waiting for admin approval.',
            'documents' => $documents->map(fn (Document $document) => $this->formatDocument($document))->values(),
        ], 201);
    }

    private function currentTeacher(Request $request): Teacher
    {
        return Teacher::where('user_id', $request->user()->id)->firstOrFail();
    }

    private function formatDocument(Document $document): array
    {
        $class = $document->classRoom;
        $subject = $class?->subject?->name ?? $class?->name ?? 'Class document';

        return [
            'id' => $document->id,
            'title' => $document->title,
            'subject' => $subject,
            'class_id' => $document->class_id,
            'class_name' => $class?->name,
            'type' => $this->fileTypeGroup($document->file_type),
            'ext' => $document->file_type,
            'status' => $document->status,
            'size' => $this->formatSize($document->file_size),
            'file_size' => $document->file_size,
            'date' => optional($document->created_at)->toIso8601String(),
            'comment' => $document->rejected_reason,
            'original_name' => $document->original_name,
        ];
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
