<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TeacherDocumentController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user()?->teacher;
        abort_unless($teacher, 404, 'Teacher profile not found.');

        $baseQuery = Document::where('teacher_id', $teacher->id);
        $counts = [
            'all' => (clone $baseQuery)->count(),
            Document::STATUS_PENDING => (clone $baseQuery)->where('status', Document::STATUS_PENDING)->count(),
            Document::STATUS_APPROVED => (clone $baseQuery)->where('status', Document::STATUS_APPROVED)->count(),
            Document::STATUS_REJECTED => (clone $baseQuery)->where('status', Document::STATUS_REJECTED)->count(),
        ];

        $documents = Document::with(['classRoom.subject'])
            ->where('teacher_id', $teacher->id)
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->status))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . $request->q . '%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('title', 'like', $term)
                        ->orWhere('original_name', 'like', $term)
                        ->orWhereHas('classRoom', fn($classQuery) => $classQuery->where('name', 'like', $term));
                });
            })
            ->latest()
            ->paginate(20)
            ->appends($request->all());

        return view('teacher.documents.index', compact('documents', 'counts'));
    }

    public function create(Request $request)
    {
        $teacher = $request->user()?->teacher;
        abort_unless($teacher, 404, 'Teacher profile not found.');

        $classes = ClassRoom::with('subject')
            ->where('teacher_id', $teacher->id)
            ->orderBy('name')
            ->get();

        return view('teacher.documents.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $teacher = $request->user()?->teacher;
        abort_unless($teacher, 404, 'Teacher profile not found.');

        $data = $request->validate([
            'class_id' => [
                'required',
                Rule::exists('classes', 'id')->where(fn($query) => $query->where('teacher_id', $teacher->id)),
            ],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx|max:51200',
        ]);

        $file = $request->file('file');
        $path = $file->store('teacher-documents');

        Document::create([
            'class_id' => $data['class_id'],
            'teacher_id' => $teacher->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => strtolower($file->getClientOriginalExtension()),
            'file_size' => $file->getSize(),
            'status' => Document::STATUS_PENDING,
        ]);

        return redirect()
            ->route('teacher.documents.index')
            ->with('success', 'Document uploaded and waiting for admin approval.');
    }

    public function download(Request $request, Document $document)
    {
        $teacher = $request->user()?->teacher;
        abort_unless($teacher && $document->isOwnedByTeacher($teacher), 403);
        abort_unless(Storage::exists($document->file_path), 404);

        return Storage::download($document->file_path, $document->original_name);
    }
}
