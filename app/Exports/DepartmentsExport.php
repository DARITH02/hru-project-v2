<?php

namespace App\Exports;

use App\Models\Department;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DepartmentsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Department::withCount(['teachers', 'subjects'])->get();
    }

    public function headings(): array
    {
        return ['ID', 'Code', 'Department Name', 'Instructors Count', 'Subjects Count'];
    }

    public function map($dept): array
    {
        return [
            $dept->id,
            $dept->code,
            $dept->name,
            $dept->teachers_count,
            $dept->subjects_count
        ];
    }
}
