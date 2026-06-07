<?php

namespace App\Exports;

use App\Models\Subject;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SubjectsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Subject::with('department')->withCount('classes')->get();
    }

    public function headings(): array
    {
        return ['ID', 'Code', 'Subject Name', 'Department', 'Classes Count'];
    }

    public function map($subject): array
    {
        return [
            $subject->id,
            $subject->code,
            $subject->name,
            $subject->department->name ?? 'N/A',
            $subject->classes_count
        ];
    }
}
