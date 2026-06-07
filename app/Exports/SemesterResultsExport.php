<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SemesterResultsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $data;
    protected $academicYear;
    protected $semester;

    public function __construct($data, $academicYear, $semester)
    {
        $this->data = $data;
        $this->academicYear = $academicYear;
        $this->semester = $semester;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            ['INSTITUTIONAL SEMESTER TRANSCRIPT'],
            ['Academic Year: ' . $this->academicYear . ' | Semester: ' . $this->semester],
            [''],
            [
                'STUDENT NAME',
                'STUDENT CODE',
                'MAJOR / GROUP',
                'SUBJECTS',
                'AVG SCORE',
                'GRADE',
                'STATUS'
            ]
        ];
    }

    public function map($row): array
    {
        return [
            $row['student_name'],
            $row['student_code'],
            $row['group_name'] ?? 'N/A',
            $row['total_subjects'],
            round($row['avg_score'], 1),
            $row['grade'],
            $row['status']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            4 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'EEEEEE']
                ]
            ],
        ];
    }
}
