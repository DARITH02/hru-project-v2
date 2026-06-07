<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InstitutionalTranscriptExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $data;
    protected $subjects;
    protected $academicYear;
    protected $semester;
    protected $rowCount = 0;

    public function __construct($data, $subjects, $academicYear, $semester)
    {
        $this->data = $data;
        $this->subjects = $subjects;
        $this->academicYear = $academicYear;
        $this->semester = $semester;
    }

    public function collection()
    {
        return $this->data;
    }

    public function map($row): array
    {
        $this->rowCount++;
        $mapped = [
            $this->rowCount,
            $row['name'],
            $row['code'],
            $row['group'],
        ];

        foreach ($this->subjects as $subj) {
            $mapped[] = $row['subj_' . $subj->id];
        }

        $mapped[] = $row['avg'];
        $mapped[] = $row['grade'];
        $mapped[] = $row['status'];

        return $mapped;
    }

    public function headings(): array
    {
        $subjectHeadings = [];
        foreach ($this->subjects as $subj) {
            $subjectHeadings[] = strtoupper($subj->name);
        }

        return [
            ['INSTITUTIONAL SEMESTER TRANSCRIPT'],
            ['Academic Year: ' . $this->academicYear . ' | Semester: ' . $this->semester],
            [''],
            array_merge(
                ['NO.', 'STUDENT NAME', 'STUDENT CODE', 'MAJOR / GROUP'],
                $subjectHeadings,
                ['AVG SCORE', 'GRADE', 'STATUS']
            )
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $totalColumns = 1 + 3 + count($this->subjects) + 3;
        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalColumns);
        
        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->mergeCells("A2:{$lastColumn}2");
        
        return [
            1 => ['font' => ['bold' => true, 'size' => 16], 'alignment' => ['horizontal' => 'center']],
            2 => ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => 'center']],
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4B5563']
                ]
            ],
            'A' => ['alignment' => ['horizontal' => 'center']],
        ];
    }
}
