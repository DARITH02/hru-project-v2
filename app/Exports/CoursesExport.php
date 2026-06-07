<?php

namespace App\Exports;

use App\Models\ClassRoom;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CoursesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return ClassRoom::with(['subject', 'teacher.user', 'group'])->get();
    }

    public function headings(): array
    {
        return ['ID', 'Subject Code', 'Subject Name', 'Teacher', 'Group', 'Room', 'Schedule', 'Status'];
    }

    public function map($class): array
    {
        return [
            $class->id,
            $class->subject->code ?? 'N/A',
            $class->subject->name ?? 'N/A',
            $class->teacher->user->name ?? 'N/A',
            $class->group->name ?? 'N/A',
            $class->room_number,
            $class->schedule,
            $class->status
        ];
    }
}
