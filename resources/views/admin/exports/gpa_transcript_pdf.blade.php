<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            margin: 0;
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.45;
        }

        .page {
            padding: 28px 32px;
        }

        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #111827;
            padding-bottom: 16px;
            margin-bottom: 18px;
        }

        .header-main,
        .header-meta {
            display: table-cell;
            vertical-align: top;
        }

        .header-meta {
            text-align: right;
            width: 34%;
            color: #4b5563;
            font-size: 10px;
        }

        .eyebrow {
            color: #64748b;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        h1 {
            margin: 5px 0 2px;
            font-size: 24px;
            line-height: 1.1;
        }

        .student-code {
            color: #4b5563;
            font-size: 10px;
            font-weight: bold;
        }

        .summary {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin: 0 -8px 22px;
        }

        .summary-cell {
            display: table-cell;
            width: 25%;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 11px 12px;
            background: #f8fafc;
        }

        .summary-cell span {
            display: block;
            margin-bottom: 4px;
            color: #64748b;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .summary-cell strong {
            font-size: 17px;
        }

        .term {
            margin-top: 18px;
            page-break-inside: avoid;
        }

        .term-head {
            background: #111827;
            color: #fff;
            padding: 9px 11px;
            font-size: 11px;
            font-weight: bold;
        }

        .term-head span {
            float: right;
            font-weight: normal;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 8px 9px;
            border: 1px solid #d1d5db;
            background: #f3f4f6;
            color: #374151;
            font-size: 8px;
            letter-spacing: .08em;
            text-align: left;
            text-transform: uppercase;
        }

        td {
            padding: 8px 9px;
            border: 1px solid #e5e7eb;
        }

        .num {
            text-align: right;
            font-weight: bold;
        }

        .footer {
            margin-top: 26px;
            border-top: 1px solid #d1d5db;
            padding-top: 10px;
            color: #64748b;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="header-main">
                <div class="eyebrow">{{ \App\Models\Setting::get('app_name', 'HRU') }} {{ __('admin.gpa_transcripts.official_transcript') }}</div>
                <h1>{{ $latest->student_name }}</h1>
                <div class="student-code">{{ $latest->student_code }}</div>
            </div>
            <div class="header-meta">
                <div>{{ $latest->major_name ?: __('admin.gpa_transcripts.general_program') }}</div>
                <div>{{ $latest->class_group_name ?: __('admin.gpa_transcripts.unassigned_group') }}</div>
                <div>{{ __('admin.gpa_transcripts.generated') }}: {{ now()->format('Y-m-d H:i') }}</div>
            </div>
        </div>

        <div class="summary">
            <div class="summary-cell">
                <span>{{ __('admin.gpa_transcripts.cumulative_gpa') }}</span>
                <strong>{{ number_format((float) $latest->cumulative_gpa, 2) }}</strong>
            </div>
            <div class="summary-cell">
                <span>{{ __('admin.gpa_transcripts.total_credits') }}</span>
                <strong>{{ number_format((float) $latest->cumulative_credits, 2) }}</strong>
            </div>
            <div class="summary-cell">
                <span>{{ __('admin.gpa_transcripts.latest_semester_gpa') }}</span>
                <strong>{{ number_format((float) $latest->semester_gpa, 2) }}</strong>
            </div>
            <div class="summary-cell">
                <span>{{ __('admin.gpa_transcripts.academic_standing') }}</span>
                <strong>{{ $standing }}</strong>
            </div>
        </div>

        @foreach ($histories as $history)
            <section class="term">
                <div class="term-head">
                    {{ $history->academic_year }} - {{ __('admin.gpa_transcripts.semester') }} {{ $history->semester }}
                    <span>{{ __('admin.gpa_transcripts.semester') }} {{ __('admin.gpa_transcripts.gpa') }} {{ number_format((float) $history->semester_gpa, 2) }}</span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 16%">{{ __('admin.gpa_transcripts.code') }}</th>
                            <th>{{ __('admin.gpa_transcripts.subject') }}</th>
                            <th style="width: 12%">{{ __('admin.gpa_transcripts.credit') }}</th>
                            <th style="width: 12%">{{ __('admin.gpa_transcripts.score') }}</th>
                            <th style="width: 12%">{{ __('admin.gpa_transcripts.grade') }}</th>
                            <th style="width: 12%">{{ __('admin.gpa_transcripts.point') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history->subjectGrades as $grade)
                            <tr>
                                <td>{{ $grade->subject_code ?: 'SUBJ' }}</td>
                                <td>{{ $grade->subject_name }}</td>
                                <td class="num">{{ number_format((float) $grade->credit, 2) }}</td>
                                <td class="num">{{ number_format((float) $grade->total_score, 2) }}</td>
                                <td class="num">{{ $grade->letter_grade }}</td>
                                <td class="num">{{ number_format((float) $grade->grade_point, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endforeach

        <div class="footer">
            {{ __('admin.gpa_transcripts.pdf_footer') }}
        </div>
    </div>
</body>
</html>
