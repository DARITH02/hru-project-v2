<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SubjectScoresExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $data;
    protected $subjectName;

    public function __construct($data, $subjectName)
    {
        $this->data = $data;
        $this->subjectName = $subjectName;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            ['Subject:', $this->subjectName],
            ['Generated on:', now()->format('Y-m-d H:i')],
            [],
            ['Student ID', 'Student Name', 'Attendance (20)', 'Midterm (15)', 'Assignment (15)', 'Final (50)', 'Total (100)']
        ];
    }

    public function map($row): array
    {
        return [
            $row['code'],
            $row['name'],
            $row['att_score'],
            $row['midterm'],
            $row['assignment'],
            $row['final'],
            $row['total']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4B5563']]],
        ];
    }
}
