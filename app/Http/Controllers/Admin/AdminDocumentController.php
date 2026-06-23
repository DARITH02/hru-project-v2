<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminDocumentController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = Document::query();
        $counts = [
            'all' => (clone $baseQuery)->count(),
            Document::STATUS_PENDING => (clone $baseQuery)->where('status', Document::STATUS_PENDING)->count(),
            Document::STATUS_APPROVED => (clone $baseQuery)->where('status', Document::STATUS_APPROVED)->count(),
            Document::STATUS_REJECTED => (clone $baseQuery)->where('status', Document::STATUS_REJECTED)->count(),
        ];

        $documents = Document::with(['teacher.user', 'classRoom.subject', 'approver'])
            ->where('status', $request->input('status', Document::STATUS_PENDING))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . $request->q . '%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('title', 'like', $term)
                        ->orWhere('original_name', 'like', $term)
                        ->orWhereHas('teacher.user', fn($teacherQuery) => $teacherQuery->where('name', 'like', $term))
                        ->orWhereHas('classRoom', fn($classQuery) => $classQuery->where('name', 'like', $term));
                });
            })
            ->latest()
            ->get();

        $documentsByTeacher = $documents
            ->sortBy(fn(Document $document) => $document->teacher->user->name ?? '')
            ->groupBy(fn(Document $document) => $document->teacher->user->name ?? __('admin.documents.unknown_teacher'));

        return view('admin.documents.index', compact('documents', 'documentsByTeacher', 'counts'));
    }

    public function preview(Document $document)
    {
        abort_unless(Storage::exists($document->file_path), 404);

        return response()->file(Storage::path($document->file_path));
    }

    public function download(Document $document)
    {
        abort_unless(Storage::exists($document->file_path), 404);

        return Storage::download($document->file_path, $document->original_name);
    }

    public function approve(Request $request, Document $document)
    {
        $document->update([
            'status' => Document::STATUS_APPROVED,
            'approved_by' => $request->user()?->id,
            'approved_at' => now(),
            'rejected_reason' => null,
        ]);

        return back()->with('success', 'Document approved.');
    }

    public function reject(Request $request, Document $document)
    {
        $data = $request->validate([
            'rejected_reason' => 'required|string|max:2000',
        ]);

        $document->update([
            'status' => Document::STATUS_REJECTED,
            'approved_by' => null,
            'approved_at' => null,
            'rejected_reason' => $data['rejected_reason'],
        ]);

        return back()->with('success', 'Document rejected.');
    }

    public function destroyRejected(Request $request)
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $deleted = 0;

        Document::where('status', Document::STATUS_REJECTED)
            ->orderBy('id')
            ->chunkById(100, function ($documents) use (&$deleted) {
                foreach ($documents as $document) {
                    if ($document->file_path && Storage::exists($document->file_path)) {
                        Storage::delete($document->file_path);
                    }

                    $document->delete();
                    $deleted++;
                }
            });

        return redirect()
            ->route('admin.documents.index', ['status' => Document::STATUS_REJECTED])
            ->with('success', __('admin.documents.deleted_rejected_count', ['count' => $deleted]));
    }
}
