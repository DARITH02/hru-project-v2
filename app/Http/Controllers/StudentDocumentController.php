<?php

namespace App\Http\Controllers;

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
            ->filter(fn(Document $document) => $document->isVisibleToStudent($student))
            ->values();

        return view('student.documents.index', compact('documents'));
    }

    public function download(Request $request, Document $document)
    {
        $student = $request->user()?->student;
        abort_unless($student && $document->isVisibleToStudent($student), 403);
        abort_unless(Storage::exists($document->file_path), 404);

        return Storage::download($document->file_path, $document->original_name);
    }
}
