<?php

namespace App\Exports;

use App\Models\ClassGroup;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GroupsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return ClassGroup::with(['major.department'])->withCount('students')->get();
    }

    public function headings(): array
    {
        return ['ID', 'Group/Class Name', 'Year Level', 'Major', 'Department', 'Students Count'];
    }

    public function map($group): array
    {
        return [
            $group->id,
            $group->name,
            $group->year_level,
            $group->major->name ?? 'N/A',
            $group->major->department->name ?? 'N/A',
            $group->students_count
        ];
    }
}
