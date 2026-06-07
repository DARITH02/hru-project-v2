<?php

namespace App\Exports;

use App\Models\Teacher;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InstructorsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Teacher::with(['user', 'department'])->get();
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Email', 'Department', 'Phone', 'Status', 'Classes Count'];
    }

    public function map($teacher): array
    {
        return [
            $teacher->id,
            $teacher->user->name,
            $teacher->user->email,
            $teacher->department->name ?? 'N/A',
            $teacher->user->phone,
            $teacher->status,
            $teacher->classes()->count()
        ];
    }
}
